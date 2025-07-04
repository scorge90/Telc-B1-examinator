import json
import string
from spellchecker import SpellChecker

def preprocess_words(text: str):
    """
    Splits text into words and strips punctuation from each word.
    """
    translator = str.maketrans('', '', string.punctuation)
    words = text.split()
    cleaned_words = [w.translate(translator) for w in words if w.strip()]
    return cleaned_words

def count_orthographic_errors(text: str) -> int:
    """
    Counts the number of orthographic errors in a German text using pyspellchecker.

    Args:
        text (str): The text to check.

    Returns:
        int: Number of misspelled words.
    """
    spell = SpellChecker(language='de')
    words = preprocess_words(text)
    misspelled = spell.unknown(words)
    return len(misspelled)

def get_misspelled_words(text: str):
    """
    Returns the set of misspelled words in a German text using pyspellchecker.

    Args:
        text (str): The text to check.

    Returns:
        set: Misspelled words.
    """
    spell = SpellChecker(language='de')
    words = preprocess_words(text)
    misspelled = spell.unknown(words)
    return misspelled

if __name__ == "__main__":
    import sys

    input_file = sys.argv[1] if len(sys.argv) > 1 else 'input.json'

    try:
        with open(input_file, encoding='utf-8') as f:
            data = json.load(f)
        student_text = data.get("student_text", "")
        if not student_text:
            print("No 'student_text' found in input JSON.")
            sys.exit(1)

        errors = count_orthographic_errors(student_text)
        misspelled_words = get_misspelled_words(student_text)

        print(f"Number of orthographic errors: {errors}")
        print(f"Misspelled words: {misspelled_words}")

    except Exception as e:
        print(f"Error reading input or counting errors: {e}")
        sys.exit(1)

