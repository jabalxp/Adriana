<?php
session_start();
require_once 'require.php';

// Verificar se tem permissão (Administrador, Gerente, Auxiliar)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_nivel'], ['Administrador', 'Gerente', 'Auxiliar'])) {
    header("Location: ../views/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];

    // Validar extensão
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (strtolower($ext) !== 'xlsx') {
        header("Location: ../views/docente/importacao.php?error=Apenas arquivos .xlsx são permitidos");
        exit();
    }

    $uploadDir = '../uploads/temp/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $tempFile = $uploadDir . uniqid() . '.xlsx';

    if (move_uploaded_file($file['tmp_name'], $tempFile)) {
        $alunos_importados = [];
        $extractDir = $uploadDir . 'xlsx_' . uniqid();
        $tempFileAbs = realpath($tempFile);
        
        $success = false;
        
        // --- MÉTODO 1: tar -xf (Disponível no Windows 10/11 moderno) ---
        if (!$success) {
            if (!is_dir($extractDir)) mkdir($extractDir, 0777, true);
            $extractDirAbs = realpath($extractDir);
            // O comando 'tar' no Windows é muito versátil e não exige a extensão .zip
            $cmd = "tar -xf " . escapeshellarg($tempFileAbs) . " -C " . escapeshellarg($extractDirAbs) . " 2>&1";
            shell_exec($cmd);
            
            if (file_exists($extractDir . '/xl/worksheets/sheet1.xml')) {
                $success = true;
            }
        }

        // --- MÉTODO 2: PowerShell Expand-Archive (Com renomeação para .zip) ---
        if (!$success) {
            $zipTempFile = $tempFile . '.zip';
            copy($tempFile, $zipTempFile);
            $zipTempFileAbs = realpath($zipTempFile);
            
            if (!is_dir($extractDir)) mkdir($extractDir, 0777, true);
            $extractDirAbs = realpath($extractDir);
            
            $cmd = "powershell -Command \"Expand-Archive -Path " . escapeshellarg($zipTempFileAbs) . " -DestinationPath " . escapeshellarg($extractDirAbs) . " -Force\" 2>&1";
            shell_exec($cmd);
            
            if (file_exists($extractDir . '/xl/worksheets/sheet1.xml')) {
                $success = true;
            }
            
            if (file_exists($zipTempFile)) unlink($zipTempFile);
        }

        // --- MÉTODO 3: ZipArchive (Backup caso habilitado) ---
        if (!$success && class_exists('ZipArchive')) {
            $zip = new ZipArchive;
            if ($zip->open($tempFile) === TRUE) {
                if (!is_dir($extractDir)) mkdir($extractDir, 0777, true);
                $zip->extractTo($extractDir);
                $zip->close();
                $success = true;
            }
        }

        if ($success) {
            // 1. Carregar Shared Strings (textos do Excel)
            $sharedStrings = [];
            $ssPath = $extractDir . '/xl/sharedStrings.xml';
            if (file_exists($ssPath)) {
                $ssXml = simplexml_load_file($ssPath);
                if ($ssXml) {
                    foreach ($ssXml->si as $si) {
                        // O texto pode estar em <t> ou em <r><t>
                        $val = "";
                        if (isset($si->t)) {
                            $val = (string)$si->t;
                        } elseif (isset($si->r)) {
                            foreach ($si->r as $r) {
                                $val .= (string)$r->t;
                            }
                        }
                        $sharedStrings[] = $val;
                    }
                }
            }

            // 2. Carregar a Planilha 1
            $sheetPath = $extractDir . '/xl/worksheets/sheet1.xml';
            if (file_exists($sheetPath)) {
                $sheetXml = simplexml_load_file($sheetPath);
                if ($sheetXml) {
                    $rows = [];
                    foreach ($sheetXml->sheetData->row as $row) {
                        $rowData = [];
                        foreach ($row->c as $c) {
                            $val = (string)$c->v;
                            $type = (string)$c['t'];
                            
                            if ($type === 's') {
                                $val = $sharedStrings[(int)$val] ?? "";
                            }
                            
                            $cellRef = (string)$c['r'];
                            $colLetter = preg_replace('/[0-9]/', '', $cellRef);
                            $rowData[$colLetter] = $val;
                        }
                        $rows[] = $rowData;
                    }

                    if (!empty($rows)) {
                        // Detectar Cabeçalhos (Normalização agressiva)
                        $headerRow = $rows[0];
                        $colMap = [];
                        
                        foreach ($headerRow as $col => $val) {
                            // Limpeza total: minúsculas, remove espaços extras e quebras de linha
                            $normVal = strtolower(trim(preg_replace('/\s+/', ' ', $val)));
                            
                            if (strpos($normVal, 'turma') !== false && strpos($normVal, 'sigla') !== false) {
                                $colMap['turma'] = $col;
                            } elseif (strpos($normVal, 'nome') !== false && strpos($normVal, 'curso') !== false) {
                                $colMap['curso'] = $col;
                            } elseif (strpos($normVal, 'nome') !== false && (strpos($normVal, 'aluno') !== false || strpos($normVal, 'estudante') !== false)) {
                                $colMap['nome'] = $col;
                            }
                        }

                        // Caso de fallback manual se os nomes não baterem exatamente mas tivermos colunas
                        if (!isset($colMap['turma']) && isset($headerRow['A'])) $colMap['turma'] = 'A';
                        if (!isset($colMap['curso']) && isset($headerRow['B'])) $colMap['curso'] = 'B';
                        if (!isset($colMap['nome']) && isset($headerRow['C'])) $colMap['nome'] = 'C';

                        $hasHeaders = isset($colMap['turma'], $colMap['nome']);
                        $startIdx = 1; // Sempre pula a primeira linha se estamos usando colMap

                        for ($i = $startIdx; $i < count($rows); $i++) {
                            $row = $rows[$i];
                            $nome = $row[$colMap['nome'] ?? ''] ?? "";
                            $turma = $row[$colMap['turma'] ?? ''] ?? "";
                            $curso = $row[$colMap['curso'] ?? ''] ?? "";

                            if (!empty($nome) && !empty($turma)) {
                                $alunos_importados[] = [
                                    'nome' => trim($nome),
                                    'turma' => trim($turma),
                                    'curso' => trim($curso)
                                ];
                            }
                        }
                    }
                }
            }
        }

        // --- LIMPEZA ---
        if (file_exists($tempFile)) unlink($tempFile);
        
        if (is_dir($extractDir)) {
            $it = new RecursiveDirectoryIterator($extractDir, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $fileinfo) {
                $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                $todo($fileinfo->getRealPath());
            }
            rmdir($extractDir);
        }

        if (empty($alunos_importados)) {
            $errorInfo = $success ? "Nenhum dado válido encontrado." : "Falha na extração do arquivo Excel.";
            header("Location: ../views/docente/importacao.php?error=" . urlencode("Erro: " . $errorInfo));
            exit();
        }

        try {
            mysqli_begin_transaction($conn);
            $count = 0;

            foreach ($alunos_importados as $aluno_data) {
                $nome_aluno = $aluno_data['nome'];
                $nome_turma = $aluno_data['turma'];
                $nome_curso = !empty($aluno_data['curso']) ? $aluno_data['curso'] : $nome_turma;
                $periodo = 'Integral';
                
                $email_aluno = strtolower(preg_replace('/[^A-Za-z0-9]/', '.', $nome_aluno)) . '@aluno.senai.br';

                // 1. Curso
                $stmt = mysqli_prepare($conn, "INSERT IGNORE INTO cursos (nome) VALUES (?)");
                mysqli_stmt_bind_param($stmt, "s", $nome_curso);
                mysqli_stmt_execute($stmt);

                $stmt = mysqli_prepare($conn, "SELECT id FROM cursos WHERE nome = ?");
                mysqli_stmt_bind_param($stmt, "s", $nome_curso);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row_curso = mysqli_fetch_assoc($result);
                $curso_id = $row_curso['id'] ?? null;

                if (!$curso_id) continue;

                // 2. Turma
                $stmt = mysqli_prepare($conn, "SELECT id FROM turmas WHERE nome = ? AND curso_id = ?");
                mysqli_stmt_bind_param($stmt, "si", $nome_turma, $curso_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row_turma = mysqli_fetch_assoc($result);
                $turma_id = $row_turma['id'] ?? null;

                if (!$turma_id) {
                    $stmt = mysqli_prepare($conn, "INSERT INTO turmas (nome, curso_id, periodo) VALUES (?, ?, ?)");
                    mysqli_stmt_bind_param($stmt, "sis", $nome_turma, $curso_id, $periodo);
                    mysqli_stmt_execute($stmt);
                    $turma_id = mysqli_insert_id($conn);
                }

                if (!$turma_id) continue;

                // 3. Aluno
                $stmt = mysqli_prepare($conn, "INSERT INTO alunos (nome, email_pessoal, turma_id) 
                                        VALUES (?, ?, ?) 
                                        ON DUPLICATE KEY UPDATE nome = VALUES(nome), turma_id = VALUES(turma_id)");
                mysqli_stmt_bind_param($stmt, "ssi", $nome_aluno, $email_aluno, $turma_id);
                mysqli_stmt_execute($stmt);

                $count++;
            }

            mysqli_commit($conn);
            header("Location: ../views/docente/importacao.php?success=" . $count);
            exit();

        } catch (Exception $e) {
            mysqli_rollback($conn);
            header("Location: ../views/docente/importacao.php?error=" . urlencode($e->getMessage()));
            exit();
        }
    } else {
        header("Location: ../views/docente/importacao.php?error=Falha no upload do arquivo");
        exit();
    }
}
?>
