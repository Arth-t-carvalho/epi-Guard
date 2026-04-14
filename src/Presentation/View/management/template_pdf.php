<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Modelo de Cadastro de Funcionários - Facchini</title>
    <style>
        :root {
            --primary: #E30613;
            --secondary: #06377c;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 40px;
            color: #333;
            background: #fff;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo-text {
            font-size: 28px;
            font-weight: 900;
            color: var(--primary);
            letter-spacing: -1px;
        }
        .logo-text span {
            color: var(--secondary);
        }
        .title {
            text-align: right;
        }
        .title h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }
        .title p {
            margin: 5px 0 0 0;
            font-size: 12px;
            color: #666;
        }
        .instructions {
            background: #f8f9fa;
            border-left: 4px solid var(--secondary);
            padding: 15px;
            margin-bottom: 30px;
            font-size: 13px;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 14px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 800;
            color: #555;
            letter-spacing: 0.5px;
        }
        td {
            height: 30px;
        }
        .footer {
            margin-top: 50px;
            font-size: 10px;
            color: #999;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .form-info {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        .info-box {
            font-size: 13px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .info-box span {
            font-weight: 800;
            color: var(--secondary);
            font-size: 11px;
            text-transform: uppercase;
            display: block;
            margin-bottom: 5px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 20px;
            }
            .header {
                border-bottom-width: 3px;
            }
        }
        .btn-print {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 800;
            font-size: 14px;
            margin-bottom: 20px;
            box-shadow: 0 4px 14px rgba(227, 6, 19, 0.3);
            transition: 0.2s;
        }
        .btn-print:hover {
            transform: translateY(-2px);
            background: #c40510;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 40px;">
        <button class="btn-print" onclick="window.print()">🖨️ IMPRIMIR / SALVAR COMO PDF</button>
        <p style="font-size: 13px; color: #666; font-family: 'Inter', sans-serif;">
            Utilize este modelo para coletar dados no campo. <br>
            Depois, basta anexar o arquivo no sistema para registrar os colaboradores automaticamente.
        </p>
    </div>

    <div class="header">
        <div class="logo-text">FACCHINI<span>.</span></div>
        <div class="title">
            <h1>Relatório de Lançamento de Colaboradores</h1>
            <p>Gerenciamento epi-Guard - SST</p>
        </div>
    </div>

    <div class="form-info">
        <div class="info-box">
            <span>Unidade Operacional</span>
            ___________________________
        </div>
        <div class="info-box">
            <span>Setor de Alocação</span>
            ___________________________
        </div>
        <div class="info-box">
            <span>Data de Emissão</span>
            ____/____/_______
        </div>
    </div>

    <div class="instructions">
        <strong>Instruções:</strong> Preencha os campos abaixo com letra de forma. O campo "ID / CPF" é opcional, mas ajuda a evitar duplicatas. 
        Este documento é um suporte oficial para o cadastro rápido no sistema epi-Guard.
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px; text-align: center;">Nº</th>
                <th>Nome Completo (Mín. 5 caracteres)</th>
                <th style="width: 180px;">Siga / Registro (Opcional)</th>
                <th style="width: 150px;">Observações</th>
                <th style="width: 120px; text-align: center;">Assinatura</th>
            </tr>
        </thead>
        <tbody>
            <?php for($i=1; $i<=22; $i++): ?>
            <tr>
                <td style="text-align: center; font-size: 11px; color: #999; font-weight: 700;"><?= sprintf('%02d', $i) ?></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <div class="footer">
        Este documento faz parte do Sistema Integrado de Segurança do Trabalho - Facchini S/A. <br>
        <strong>Facchini. Todos os direitos reservados. 2026.</strong>
    </div>

</body>
</html>
