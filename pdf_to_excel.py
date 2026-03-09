import pdfplumber
import pandas as pd
import re

pdf_path = "c:\\xampp\\htdocs\\Adriana\\arq_169_250203_62575440-60ef-4f45-bbd1-1a904dc18c44.pdf"
excel_path = "c:\\xampp\\htdocs\\Adriana\\alunos_extraidos.xlsx"

all_data = []

with pdfplumber.open(pdf_path) as pdf:
    for page in pdf.pages:
        text = page.extract_text()
        if text:
            lines = text.split('\n')
            for line in lines:
                line = line.strip()
                if not line:
                    continue
                # Split by 2 or more spaces
                parts = re.split(r'\s{2,}', line)
                if len(parts) >= 2:
                    all_data.append(parts)
                else:
                    # If single spaces, maybe we can just split all and try to guess
                    # As a fallback:
                    words = line.split(' ')
                    if len(words) > 1:
                        all_data.append([" ".join(words)])

if all_data:
    # Ensure all rows have same length by padding or truncating if needed, but let's just dump
    df = pd.DataFrame(all_data)
    df.to_excel(excel_path, index=False, header=False, engine='openpyxl')
    print(f"Arquivo salvo com sucesso em: {excel_path}")
else:
    print("Nenhum dado encontrado no PDF.")
