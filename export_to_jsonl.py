import pymysql
import json
import decimal

def convert_decimals(obj):
    """Recursively convert Decimals to float or int for JSON serialization."""
    if isinstance(obj, list):
        return [convert_decimals(i) for i in obj]
    elif isinstance(obj, dict):
        return {k: convert_decimals(v) for k, v in obj.items()}
    elif isinstance(obj, decimal.Decimal):
        return float(obj)
    else:
        return obj

# Connect to your database
conn = pymysql.connect(
    host="localhost",
    user="root",
    password="",
    database="text_b1_bewertung",
    charset="utf8mb4"
)

cursor = conn.cursor(pymysql.cursors.DictCursor)

# Query relevant rows
cursor.execute("""
    SELECT 
        template_letter,
        student_text,
        corrected_text,
        comments,
        grammar_score,
        grammar_comment,
        vocabulary_score,
        vocabulary_comment,
        style_score,
        style_comment,
        content_score,
        content_comment,
        overall_score,
        overall_comment,
        ergebnis,
        word_count,
        error_counts
    FROM b1_texts
    WHERE student_text IS NOT NULL AND overall_score IS NOT NULL
""")

# Output file
with open("training_data.jsonl", "w", encoding="utf-8") as f:
    for row in cursor.fetchall():
        # Build structured JSON output
        grades = {
            "Grammatik": {
                "note": float(row["grammar_score"]),
                "kommentar": row["grammar_comment"] or ""
            },
            "Wortschatz": {
                "note": float(row["vocabulary_score"]),
                "kommentar": row["vocabulary_comment"] or ""
            },
            "Stil": {
                "note": float(row["style_score"]),
                "kommentar": row["style_comment"] or ""
            },
            "Inhalt": {
                "note": float(row["content_score"]),
                "kommentar": row["content_comment"] or ""
            },
            "Gesamtnote": {
                "note": float(row["overall_score"]),
                "kommentar": row["overall_comment"] or "",
                "ergebnis": row["ergebnis"] or ""
            }
        }

        entry = {
            "messages": [
                {
                    "role": "system",
                    "content": "You are a strict B1-level German examiner. Evaluate student letters based on Inhalt, Grammatik, Wortschatz, Stil. Give each a score out of 10, and an overall score (0–40) with pass/fail judgment. Return the result as a strict JSON object with corrected_text, word_count, comments[], error_counts{}, and grades{}."
                },
                {
                    "role": "user",
                    "content": row["student_text"]
                },
                {
                    "role": "assistant",
                    "content": json.dumps({
                        "template_letter": row["template_letter"],
                        "corrected_text": row["corrected_text"] or "",
                        "word_count": int(row["word_count"] or 0),
                        "comments": json.loads(row["comments"] or "[]"),
                        "error_counts": json.loads(row["error_counts"] or "{}"),
                        "grades": grades
                    }, ensure_ascii=False)
                }
            ]
        }

        # Convert Decimal → float in case of leftovers
        jsonl_clean = convert_decimals(entry)

        # Write to file
        f.write(json.dumps(jsonl_clean, ensure_ascii=False) + "\n")

cursor.close()
conn.close()
