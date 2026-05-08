# 📋 Checklist de Testes Manuais - Minhas Assinaturas

Este documento serve como guia para a validação manual das funcionalidades cobertas pelos 145 testes automatizados. Siga o passo a passo para garantir que o comportamento visual e lógico condiz com o esperado.

---

## 🛠️ Preparação do Ambiente
1. Certifique-se de que o servidor está rodando (Laravel Herd ou Artisan Serve).
2. O banco de dados deve estar migrado: `php artisan migrate`.
3. Recomenda-se usar o modo anônimo do navegador para testar fluxos de login/registro.

---

## 🔐 Módulo 1: Autenticação e Acesso (T05 - T11)

### Checklist e Instruções
- [ ] 001: Usuário pode ver a tela de registro
    - **Instrução**: Acesse `/register` em modo anônimo. Verifique se o formulário de cadastro carrega.
- [ ] 002: Erro ao registrar com e-mail já existente
    - **Instrução**: No cadastro, use um e-mail que já está no banco. Clique em Registrar e verifique o erro "O e-mail já está em uso".
- [ ] 003: Registro bem-sucedido com dados válidos
    - **Instrução**: Use um e-mail novo, preencha todos os campos e registre. Deve redirecionar para o Dashboard.
- [ ] 004: Usuário pode ver a tela de login
    - **Instrução**: Acesse `/login`. Verifique se os campos de E-mail e Senha estão visíveis.
- [ ] 005: Erro ao logar com credenciais inválidas
    - **Instrução**: Tente logar com um e-mail inexistente ou senha errada. Verifique o alerta de erro.
- [x] 006: Tela de registro renderiza corretamente
- [x] 007: Registro com consentimento LGPD funciona
- [x] 008: Registro sem consentimento LGPD é bloqueado
- [ ] 009: Solicitação de reset de senha renderiza
    - **Instrução**: Clique em "Esqueci minha senha" no login. Verifique se a tela de envio de link carrega.
- [ ] 010: Link de reset de senha é enviado/solicitado
    - **Instrução**: Insira seu e-mail e clique em "Enviar link". Verifique se aparece a mensagem de sucesso (confirme no log do Laravel se o e-mail "saiu").
- [ ] 011: Tela de nova senha renderiza com token
    - **Instrução**: No log do Laravel, copie o link de reset, cole no navegador e verifique se a tela de "Nova Senha" aparece.
- [ ] 012: Senha redefinida com sucesso
    - **Instrução**: Defina a nova senha, logue com ela e confirme o acesso.
- [x] 013: Tela de login renderiza
- [x] 014: Login com credenciais válidas
- [x] 015: Login falha com senha errada
- [x] 016: Redirecionamento para 2FA se ativo
- [x] 017: Logout encerra sessão
- [ ] 018: Tela de verificação de e-mail aparece
    - **Instrução**: Após registrar, se o sistema exigir verificação, verifique se a tela "Verifique seu e-mail" aparece bloqueando o acesso.
- [ ] 019: Verificação de e-mail via link
    - **Instrução**: No log, pegue o link de verificação, clique e confirme se o acesso ao Dashboard é liberado.
- [ ] 020: Erro com hash de e-mail inválido
    - **Instrução**: Tente acessar o link de verificação alterando um caractere do hash na URL. Deve dar erro 403 ou similar.
- [ ] 021: Redirecionamento de usuário já verificado
    - **Instrução**: Tente acessar o link de verificação novamente após já estar verificado. Deve redirecionar para a Home.
- [x] 022: 2FA exige autenticação prévia
- [x] 023: Desafio 2FA aparece após login correto
- [x] 024: Botão "Login com Google" redireciona
- [x] 025: Erro no Socialite retorna para login
- [x] 026: Callback Google sem e-mail falha
- [x] 027: Cadastro automático via Google
- [x] 028: Login Google para usuário já existente
- [x] 029: Confirmação de senha para áreas sensíveis


---

## 📊 Módulo 2: Assinaturas e Dashboard (T04, T17, T27, T29)

### Checklist e Instruções
- [x] 118: Listagem de assinaturas carrega
- [x] 119: Criar nova assinatura via modal/form
- [x] 120: Exportar CSV de assinaturas
- [x] 121: Neutralização de fórmulas no CSV (CSV Injection)
- [x] 122: Importar CSV com detecção de duplicatas
- [x] 123: Proteção contra inputs gigantes (> 255 chars)
- [x] 124: Busca por texto literal (SQL Injection test)
- [x] 125: HTML/XSS na listagem (deve mostrar texto puro)
- [ ] 126: Paginação: próxima página na listagem
    - **Instrução**: Crie mais de 10 assinaturas e clique em "Próxima" na base da tabela.
- [ ] 127: Paginação: página anterior
    - **Instrução**: Estando na página 2, clique em "Anterior" e verifique se volta corretamente.
- [ ] 128: Paginação: saltar para página específica
    - **Instrução**: Use os números de página (se disponíveis) para ir direto para uma página distante.
- [ ] 129: Ordenação por Nome (asc/desc)
    - **Instrução**: Clique no cabeçalho "SERVIÇO" e veja a lista alternar entre A-Z e Z-A.
- [ ] 130: Ordenação por Valor
    - **Instrução**: Clique no cabeçalho "VALOR" e verifique a ordem crescente/decrescente.
- [ ] 131: Ordenação por Próximo Vencimento
    - **Instrução**: Clique no cabeçalho "VENCIMENTO" e verifique se as contas que vencem antes aparecem primeiro.
- [ ] 132: Soma total no Dashboard ignora assinaturas canceladas
    - **Instrução**: Crie uma assinatura ativa e outra cancelada. Verifique se o valor no Dashboard ignora a cancelada.
- [ ] 133: Soma total no Dashboard converte corretamente centavos (10,50 + 10,50 = 21,00)
    - **Instrução**: Verifique se a soma das assinaturas ativa bate exatamente com o total exibido nos cards superiores.
- [ ] 134: Soma total no Dashboard lida com vírgula e ponto como separadores
    - **Instrução**: Edite assinaturas usando `10.50` e `10,50`. O sistema deve entender ambos e somar corretamente.
- [ ] 135: Indicador de "Vencido" aparece em vermelho para datas passadas
    - **Instrução**: Altere a data de vencimento de uma assinatura para ontem. A data na lista deve ficar vermelha.
- [ ] 136: Filtro de categoria mantém o estado após paginação
    - **Instrução**: Filtre por uma categoria, vá para a página 2 e verifique se o filtro continua aplicado.
- [ ] 137: Seleção de múltiplas assinaturas para exclusão
    - **Instrução**: Marque os checkboxes de 2 ou mais assinaturas. Clique no botão de excluir massa que deve aparecer.
- [ ] 138: Modal de confirmação ao excluir assinatura única
    - **Instrução**: Clique no ícone de lixeira de uma assinatura. Verifique se o navegador ou modal pede confirmação.
- [ ] 139: Nome da categoria aparece truncado se for muito longo na lista
    - **Instrução**: Use uma categoria com nome gigante e verifique se ela não "quebra" o layout da tabela.
- [x] 114: Dashboard isolado por Privacy Token
- [x] 053: Admin vê todas as assinaturas do sistema
- [ ] 054: Usuário normal não acessa assinaturas admin
    - **Instrução**: Tente acessar `/admin/servicos` com uma conta comum. Deve redirecionar para o dashboard.
- [ ] 055: Busca com SQL Injection literal tratada como string
    - **Instrução**: Pesquise por `'; DROP TABLE subscriptions; --`. O sistema deve apenas mostrar "Nenhuma assinatura encontrada" sem dar erro.
- [ ] 056: Busca com payload XSS escapada na tela
    - **Instrução**: Pesquise por `<script>alert(1)</script>`. Verifique se o termo de busca na tela aparece como texto puro.
- [ ] 057: Ordenação por campo inválido usa padrão
    - **Instrução**: Esse teste é de URL. Tente forçar no navegador `?sortField=invalido`. O sistema deve carregar o padrão sem quebrar.
- [ ] 058: Filtro de categoria com ID inválido
    - **Instrução**: Tente forçar um ID de categoria que não existe na URL. O sistema deve mostrar "Nenhuma assinatura encontrada".
- [ ] 059: Filtro de status inválido retorna vazio
    - **Instrução**: Tente forçar um status inexistente na URL. O sistema deve mostrar "Nenhuma assinatura encontrada".
- [ ] 060: Paginação: página superior ao total não quebra
    - **Instrução**: Tente ir para a página 9999. O sistema deve mostrar a lista vazia ou a última página sem erro fatal.
- [ ] 061: Busca vazia retorna todos
    - **Instrução**: Digite algo na busca e depois apague. A lista completa deve retornar.
- [ ] 062: Busca match parcial
    - **Instrução**: Se tiver "Netflix", busque por "Net". Deve encontrar.
- [ ] 063: Busca insensível a maiúsculas (Case-insensitive)
    - **Instrução**: Busque "NETFLIX" em maiúsculas. Deve encontrar "Netflix".
- [ ] 064: Busca com caracteres especiais
    - **Instrução**: Se tiver "São Paulo", busque por "São". Deve encontrar corretamente.
- [ ] 065: Ordenação alterna direção ao clicar no mesmo campo
    - **Instrução**: Clique em "SERVIÇO" uma vez (A-Z), clique de novo (Z-A). Verifique a alternância.
- [ ] 066: Notas com XSS escapadas
    - **Instrução**: Crie uma assinatura com notas contendo `<script>alert(1)</script>`. Ao abrir a edição/visualização, deve mostrar o texto puro.
- [ ] 067: URL do serviço com protocolo javascript (renderizada como href)
    - **Instrução**: Tente salvar a URL como `javascript:alert(1)`. O sistema deve neutralizar ou não permitir que o clique execute o script.
- [ ] 068: Combinação de múltiplos filtros (Busca + Status + Categoria)
    - **Instrução**: Filtre por nome "Netflix" + Status "Ativo" + Categoria "Streaming". Deve mostrar apenas o resultado exato.
- [ ] 069: Paginação: página anterior na primeira página
    - **Instrução**: Na página 1, o botão "Anterior" deve estar desativado ou não fazer nada.
- [ ] 070: Busca com string muito longa (>1000 chars)
    - **Instrução**: Cole um texto de 1000 letras na busca. O sistema deve processar sem erro 500.
- [ ] 071: Admin vê assinaturas de todos os usuários
    - **Instrução**: Logado como Admin em `/admin/servicos`, verifique se aparecem assinaturas de e-mails diferentes do seu.


---

## 🛠️ Módulo 3: Painel Administrativo (T13 - T16, T18, T28)

### Checklist e Instruções
- [ ] 030: Registro de sessão e User-Agent
    - **Instrução**: Logue e depois verifique no banco (ou log) se o sistema salvou seu navegador e IP.
- [x] 031: Acesso aos Logs de Atividade
- [ ] 032: Filtrar logs por tipo de evento (Create, Update, Delete)
    - **Instrução**: Na tela de logs, tente filtrar apenas por "created". Deve mostrar apenas criações.
- [ ] 033: Ver detalhes de um log específico (Mudanças de campos)
    - **Instrução**: Clique para ver detalhes de um log de "update". Deve mostrar o valor antigo e o novo.
- [ ] 034: Paginação de logs funciona
    - **Instrução**: Verifique se a lista de logs no admin tem paginação e se funciona.
- [ ] 035: Busca por nome de usuário nos logs
    - **Instrução**: Pesquise o nome de um usuário nos logs. Deve filtrar apenas as ações dele.
- [x] 036: Varredura de alertas manuais
- [x] 039: Tela visual de gerenciamento de usuários
- [x] 045: Lista completa de usuários (Admin)
- [x] 048: Criar novo usuário via Admin
- [x] 050: Desativar usuário (Soft Delete)
- [x] 072: Gerenciar Categorias Globais
- [ ] 073: Usuário normal não acessa gerenciamento de categorias
    - **Instrução**: Tente acessar `/admin/categorias` com conta comum. Deve ser barrado.
- [ ] 074: Criar categoria com nome válido
    - **Instrução**: No admin, crie uma categoria "Teste". Verifique se ela aparece para todos os usuários.
- [ ] 075: Criar categoria com nome muito curto (< 3 chars)
    - **Instrução**: Tente criar categoria com nome "A". Verifique o erro de validação.
- [ ] 076: Criar categoria com nome muito longo (> 255 chars)
    - **Instrução**: Tente criar categoria com nome gigante. Verifique se o sistema lida ou bloqueia corretamente.
- [ ] 077: Criar categoria com nome payload XSS
    - **Instrução**: Crie categoria com `<script>alert(1)</script>`.
- [ ] 078: Nome XSS da categoria escapado na tela
    - **Instrução**: Após criar a categoria XSS acima, verifique se na lista ela aparece como texto puro.
- [ ] 079: Criar categoria com nome duplicado
    - **Instrução**: Tente criar uma categoria com o mesmo nome de uma já existente. Deve dar erro.
- [ ] 080: Criar categoria sem ícone
    - **Instrução**: Tente salvar uma categoria sem selecionar ícone. Deve dar erro.
- [ ] 081: Criar categoria sem cor
    - **Instrução**: Tente salvar sem definir cor. Deve dar erro.
- [ ] 082: Ícone com SQL Injection salvo como string
    - **Instrução**: Tente salvar o nome do ícone (via inspecionar elemento) como `bi bi-'; DROP TABLE...`. Deve salvar como string literal.
- [ ] 083: Cor com valor inválido
    - **Instrução**: Tente enviar uma cor que não seja hexadecimal. O sistema deve validar.
- [ ] 084: Editar categoria existente
    - **Instrução**: Altere o nome e a cor de uma categoria. Verifique se atualizou para todos.
- [ ] 085: Editar categoria: nome duplicado com outra
    - **Instrução**: Tente editar o nome de uma categoria para o nome de outra existente. Deve ser bloqueado.
- [ ] 086: Editar categoria: manter o mesmo nome
    - **Instrução**: Abra a edição e salve sem mudar o nome. Deve funcionar (unique ignore).
- [ ] 087: Deletar categoria sem assinaturas
    - **Instrução**: Exclua uma categoria que ninguém usa. Deve sumir.
- [ ] 088: Deletar categoria com assinaturas (Bloqueio)
    - **Instrução**: Tente excluir uma categoria (como "Streaming") que tenha assinaturas vinculadas. O sistema deve impedir ou avisar.
- [ ] 089: Cancelar exclusão limpa o modal
    - **Instrução**: Clique em excluir, o modal abre. Clique em Cancelar. Verifique se o estado foi limpo.
- [ ] 090: Busca por categoria
    - **Instrução**: No painel de categorias, use o campo de busca.
- [ ] 091: Busca por categoria com payload XSS
    - **Instrução**: Pesquise por script no painel de categorias. Deve escapar o texto.
- [ ] 092: Paginação: próxima página
    - **Instrução**: Teste a paginação na lista de categorias.
- [ ] 093: Paginação: página anterior na página 1
    - **Instrução**: Botão anterior deve estar inativo na página 1 das categorias.
- [ ] 094: Resetar campos limpa as entradas
    - **Instrução**: Preencha o form de categoria e clique em "Limpar" ou "Cancelar".
- [ ] 095: Slug gerado automaticamente
    - **Instrução**: Crie a categoria "TV a Cabo". Verifique no banco se o slug virou `tv-a-cabo`.
- [ ] 096: Nome da categoria com acentos e caracteres especiais
    - **Instrução**: Crie "Educação & Lazer". Deve funcionar perfeitamente.
- [ ] 097: Ícone com payload XSS renderizado com segurança
    - **Instrução**: Tente forçar um ícone malicioso e veja se ele não executa no HTML.
- [x] 116: Métricas do Painel Admin


---

## ⚙️ Módulo 4: Perfil, Senhas, 2FA e LGPD (T24 - T26)

### Checklist e Instruções
- [x] 098: Página de Gerenciamento de 2FA renderiza
- [x] 102: Alterar/Criar senha (incluindo fluxo Google)
- [x] 104: Página de Perfil (Nome/E-mail)
- [ ] 105: Alterar e-mail para um já existente falha
    - **Instrução**: No perfil, tente mudar seu e-mail para o e-mail de outro usuário já cadastrado. Deve dar erro.
- [ ] 106: Logout em outros dispositivos ao mudar senha
    - **Instrução**: Logue em dois navegadores diferentes. Mude a senha em um. Verifique se no outro a sessão foi encerrada.
- [ ] 107: Verificação de e-mail necessária após mudar e-mail
    - **Instrução**: Altere seu e-mail no perfil. Verifique se o sistema exige nova verificação do novo endereço.
- [ ] 108: Termos de Uso renderiza corretamente
    - **Instrução**: Acesse `/termos` (ou link no rodapé). Verifique se o texto dos termos aparece.
- [x] 109: Exclusão de conta (Confirmar senha e desativação)
- [ ] 110: Download de dados em formato JSON (LGPD)
    - **Instrução**: Na área de dados, escolha exportar em JSON. Verifique se o arquivo gerado é legível.
- [x] 111: Exportação LGPD (Download de todos os dados)
- [ ] 112: Revogação de consentimento LGPD
    - **Instrução**: Se houver opção, tente revogar o consentimento e veja se o sistema avisa sobre as consequências (ou bloqueia acesso).
- [ ] 113: Cache de sessão invalidado após exclusão
    - **Instrução**: Após excluir a conta, tente usar o botão "Voltar" do navegador para acessar o dashboard. Deve ser barrado.


---

## 🧪 Módulo 5: Casos Extremos (T30 - T32)

### Checklist e Instruções
- [x] 140: CSV com caracteres especiais
- [x] 143: CSV com fórmulas Excel (proteção de planilha)
- [x] 144: Bloqueio de URLs inseguras
- [ ] 145: Job de limpeza de usuários excluídos após 30 dias
    - **Instrução**: Este teste é manual de banco. Altere a data `deleted_at` de um usuário para 31 dias atrás e execute `php artisan schedule:run`. O usuário deve ser removido permanentemente.
- [ ] 141: Caracteres multibyte (UTF-8) no CSV de importação
    - **Instrução**: Importe um CSV contendo nomes com acentos (ç, á, õ) e Emojis. Devem aparecer perfeitamente na lista.
- [ ] 142: Importar CSV com colunas fora de ordem
    - **Instrução**: Pegue o CSV oficial e mude a ordem das colunas (Ex: Valor antes do Nome). Verifique se o sistema detecta corretamente ou dá erro amigável.


---
