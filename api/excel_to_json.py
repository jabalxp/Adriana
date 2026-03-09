import pandas as pd
import json
import sys
import os

def excel_to_json(file_path):
    try:
        if not os.path.exists(file_path):
            print(json.dumps({"error": "Arquivo não encontrado."}))
            return

        # Tentar ler o Excel. Supondo que a primeira coluna seja o nome e a segunda a turma (baseado na extração anterior)
        # Se o arquivo não tiver cabeçalho, header=None. Mas vamos assumir que pode ter ou não.
        df = pd.read_excel(file_path, header=None)
        
        # Filtrar linhas vazias
        df = df.dropna(how='all')
        
        alunos = []
        for index, row in df.iterrows():
            row_list = row.tolist()
            # Remover NaNs e converter para string
            row_list = [str(cell) if pd.notnull(cell) else "" for cell in row_list]
            
            nome = ""
            turma = ""

            if len(row_list) >= 2 and row_list[0] and row_list[1]:
                # Caso ideal: 2 ou mais colunas (Nome e Turma)
                # No arquivo gerado pelo pdf_to_excel.py, as vezes a primeira col é "Turma Nome"
                # se houver apenas 1 col de dados real.
                nome = row_list[1].strip()
                turma = row_list[0].strip()
            elif len(row_list) >= 1 and row_list[0]:
                # Caso de 1 coluna: "TURMA NOME"
                content = row_list[0].strip()
                parts = content.split(' ', 1) # Divide no primeiro espaço
                if len(parts) == 2:
                    turma = parts[0]
                    nome = parts[1]
                else:
                    continue # Linha inválida
            
            if nome and turma:
                # Ignorar cabeçalhos
                if "nome" in nome.lower() or "turma" in turma.lower():
                    continue
                
                alunos.append({
                    "nome": nome,
                    "turma": turma
                })
        
        print(json.dumps({"alunos": alunos}, ensure_ascii=False))

    except Exception as e:
        print(json.dumps({"error": str(e)}))

if __name__ == "__main__":
    if len(sys.argv) > 1:
        excel_to_json(sys.argv[1])
    else:
        print(json.dumps({"error": "Caminho do arquivo não fornecido."}))
