<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Política de Privacidade | Minhas Assinaturas</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <style>
        body { background-color: #0b0f19; color: #a0b0d0; line-height: 1.6; }
        .content-card { background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px; padding: 40px; margin-top: 50px; margin-bottom: 80px; }
        h1, h2, h3 { color: #fff; font-weight: 800; }
        .text-primary-custom { color: #0d6efd; }
        .highlight-box { background: rgba(13, 110, 253, 0.05); border-left: 4px solid #0d6efd; padding: 20px; border-radius: 4px; margin: 25px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="content-card shadow-lg">
                    <div class="text-center mb-5">
                        <a href="/" class="text-decoration-none d-inline-block mb-4">
                            <i class="bi bi-shield-lock text-primary fs-1"></i>
                        </a>
                        <h1 class="display-4">Política de Privacidade</h1>
                        <p class="text-secondary">Última atualização: Abril de 2026</p>
                    </div>

                    <div class="highlight-box">
                        <h3 class="h5 mb-2">Compromisso com a Privacidade</h3>
                        <p class="mb-0">Este sistema foi construído para que você tenha controle total sobre seus dados. Nem mesmo nós podemos ver quais serviços você assina ou quanto você paga.</p>
                    </div>

                    <h2 class="h4 mt-5 mb-3 text-primary-custom">1. Coleta de Dados</h2>
                    <p>Coletamos as seguintes categorias de informações:</p>
                    <ul>
                        <li><strong>Dados de Identificação:</strong> Nome e E-mail (essenciais para autenticação e envio de alertas).</li>
                        <li><strong>Dados de Assinatura:</strong> Nome do serviço, valor, ciclo de cobrança e datas (fornecidos voluntariamente por você).</li>
                        <li><strong>Logs Técnicos:</strong> Para fins de segurança, prevenção de fraudes e diagnóstico de erros, registramos automaticamente seu endereço IP, tipo de dispositivo, navegador utilizado e horários de acesso.</li>
                    </ul>

                    <h2 class="h4 mt-5 mb-3 text-primary-custom">2. Como Protegemos seus Dados</h2>
                    <p>Nossa infraestrutura foi projetada sob o princípio de <strong>Isolamento de Identidade</strong>:</p>
                    <ul>
                        <li><strong>Anonimização Administrativa:</strong> Os administradores do sistema podem visualizar estatísticas globais e dados técnicos para manutenção, mas as informações de assinaturas são armazenadas de forma desvinculada do seu perfil pessoal em nível de banco de dados. Isso significa que podemos ver que existe uma assinatura, mas não sabemos a quem ela pertence.</li>
                        <li><strong>Tokens Criptográficos:</strong> Utilizamos tokens aleatórios (UUIDs) para processar seus dados, garantindo que não haja ligação direta entre sua identidade e seus registros financeiros.</li>
                        <li><strong>Criptografia:</strong> Todas as comunicações são protegidas por SSL/TLS e as senhas são armazenadas de forma segura, sendo criptografadas com "sal e pimenta" para garantir a máxima proteção contra vazamentos.</li>
                    </ul>

                    <h2 class="h4 mt-5 mb-3 text-primary-custom">3. Uso de Cookies</h2>
                    <p>Utilizamos cookies apenas para manter sua sessão ativa e garantir que o sistema funcione corretamente (cookies essenciais). Não utilizamos cookies de rastreamento para fins publicitários de terceiros.</p>

                    <h2 class="h4 mt-5 mb-3 text-primary-custom">4. Seus Direitos (LGPD)</h2>
                    <p>Conforme a Lei Geral de Proteção de Dados (LGPD), você tem o direito de:</p>
                    <ul>
                        <li>Acessar seus dados e exportá-los a qualquer momento.</li>
                        <li><strong>Retirar seu consentimento:</strong> Você pode interromper o processamento de seus dados a qualquer momento excluindo sua conta através da seção "Gestão de Dados" nas configurações do seu perfil.</li>
                        <li>Solicitar a exclusão permanente de sua conta e todos os dados associados.</li>
                        <li>Opor-se ao processamento de dados para fins específicos (como alertas de e-mail).</li>
                    </ul>

                    <h2 class="h4 mt-5 mb-3 text-primary-custom">5. Compartilhamento de Dados</h2>
                    <p>Nós <strong>nunca</strong> vendemos ou compartilhamos seus dados financeiros ou pessoais com terceiros.</p>

                    <h2 class="h4 mt-5 mb-3 text-primary-custom">6. Alterações nesta Política</h2>
                    <p>Podemos atualizar esta política periodicamente. Notificaremos você sobre mudanças significativas através do e-mail cadastrado ou por um aviso em nosso sistema.</p>

                    <div class="mt-5 text-center">
                        <a href="/" class="btn btn-primary px-5 py-2 fw-bold" style="border-radius: 50px;">Voltar para o Início</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
