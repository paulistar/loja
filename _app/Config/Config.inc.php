<?php

if (!$WorkControlDefineConf):
    /*
     * URL DO SISTEMA
     */
    if ($_SERVER['HTTP_HOST'] == 'localhost'):
        define('BASE', 'https://localhost/charme_fitness'); //Url raiz do site no localhost
    else:
        define('BASE', 'https://www.charmefitness.com.br'); //Url raiz do site no servidor
    endif;
    define('THEME', 'charme_fitness'); //template do site
endif;

//DINAMYC THEME
if (!empty($_SESSION['WC_THEME'])):
    define('THEME', $_SESSION['WC_THEME']); //template do site
endif;

/*
 * PATCH CONFIG
 */
define('INCLUDE_PATH', BASE . '/themes/' . THEME); //Geral de inclusão (Não alterar)
define('REQUIRE_PATH', 'themes/' . THEME); //Geral de inclusão (Não alterar)

if (!$WorkControlDefineConf):
    /*
     * ADMIN CONFIG
     */
    define('ADMIN_NAME', 'Charme Fitness');  //Nome do painel de controle (Work Control)
    define('ADMIN_DESC', 'Loja de Moda Fitness Feminina e Masculina, além de Moda Íntima e Moda Praia.'); //Descrição do painel de controle (Work Control)
    define('ADMIN_MODE', 2); //1 = website / 2 = e-commerce / 3 = Imobi / 4 = EAD
    define('ADMIN_WC_CUSTOM', 1); //Habilita menu e telas customizadas
    define('ADMIN_MAINTENANCE', 0); //Manutenção
    define('ADMIN_VERSION', '3.1.4');

    /*
     * E-MAIL SERVER
     * Consulte estes dados com o serviço de hospedagem
     */
    define('MAIL_HOST', 'charmefitness.com.br'); //Servidor de e-mail
    define('MAIL_PORT', '465'); //Porta de envio
    define('MAIL_USER', 'contato@charmefitness.com.br'); //E-mail de envio
    define('MAIL_SMTP', 'contato@charmefitness.com.br'); //E-mail autenticador do envio (Geralmente igual ao MAIL_USER, exceto em serviços como AmazonSES, sendgrid...)
    define('MAIL_PASS', '*************'); //Senha do e-mail de envio
    define('MAIL_SENDER', 'Charme Fitness'); //Nome do remetente de e-mail
    define('MAIL_MODE', 'ssl'); //Encriptação para envio de e-mail [0 não parametrizar / tls / ssl] (Padrão = tls)
    define('MAIL_TESTER', 'alissonpereira1993@gmail.com'); //E-mail de testes (DEV)

    /*
     * MEDIA CONFIG
     */
    define('IMAGE_W', 1600); //Tamanho da imagem (WIDTH)
    define('IMAGE_H', 800); //Tamanho da imagem (HEIGHT)
    define('THUMB_W', 800); //Tamanho da miniatura (WIDTH) PDTS
    define('THUMB_H', 1000); //Tamanho da minuatura (HEIGHT) PDTS
    define('AVATAR_W', 500); //Tamanho da miniatura (WIDTH) USERS
    define('AVATAR_H', 500); //Tamanho da minuatura (HEIGHT) USERS
    define('SLIDE_W', 1920); //Tamanho da miniatura (WIDTH) SLIDE
    define('SLIDE_H', 600); //Tamanho da minuatura (HEIGHT) SLIDE

    /*
     * APP CONFIG
     * Habilitar ou desabilitar modos do sistema
     */
    define('APP_POSTS', 0); //Posts
    define('APP_POSTS_AMP', 0); //AMP para Posts
    define('APP_POSTS_INSTANT_ARTICLE', 0); //Instante Article FB
    define('APP_EAD', 0); //Plataforma EAD
    define('APP_SEARCH', 1); //Relatório de Pesquisas
    define('APP_PAGES', 0); //Páginas
    define('APP_COMMENTS', 1); //Comentários
    define('APP_PRODUCTS', 1); //Produtos
    define('APP_ORDERS', 1); //Pedidos
    define('APP_FREIGHTS', 1); //Fretes
    define('APP_IMOBI', 0); //Imóveis
    define('APP_SLIDE', 1); //Slide Em Destaque
    define('APP_USERS', 1); //Usuários
    define('APP_BANNERS', 1); //Banners
    define('APP_TEMPLATE', 1); //Template
    define('APP_SHIPPING_QUOTE', 1); //Cotação de frete

    /*
     * LEVEL CONFIG
     * Configura permissões do painel de controle!
     */
    define('LEVEL_WC_POSTS', 6);
    define('LEVEL_WC_COMMENTS', 6);
    define('LEVEL_WC_PAGES', 6);
    define('LEVEL_WC_SLIDES', 6);
    define('LEVEL_WC_IMOBI', 6);
    define('LEVEL_WC_PRODUCTS', 6);
    define('LEVEL_WC_PRODUCTS_ORDERS', 6);
    define('LEVEL_WC_PRODUCTS_FREIGHTS', 6);
    define('LEVEL_WC_EAD_COURSES', 6);
    define('LEVEL_WC_EAD_STUDENTS', 6);
    define('LEVEL_WC_EAD_SUPPORT', 6);
    define('LEVEL_WC_EAD_ORDERS', 6);
    define('LEVEL_WC_REPORTS', 6);
    define('LEVEL_WC_TEMPLATE', 6);
    define('LEVEL_WC_USERS', 6);
    define('LEVEL_WC_BANNERS', 6);
    define('LEVEL_WC_CONFIG_MASTER', 10);
    define('LEVEL_WC_CONFIG_API', 10);
    define('LEVEL_WC_CONFIG_CODES', 10);
    define('LEVEL_WC_SHIPPING_QUOTE', 6);

    /*
     * FB SEGMENT
     * Configura ultra segmentação de público no facebook
     * !!!! IMPORTANTE :: Para utilizar ultra segmentação de produtos e imóveis
     * é precisso antes configurar os catálogos de produtos respectivamente!
     */
    define('SEGMENT_FB_PIXEL_ID', 0); //Id do pixel de rastreamento
    define('SEGMENT_WC_USER', 0); //Enviar dados do login de usuário?
    define('SEGMENT_WC_BLOG', 0); //Ultra segmentar páginas do BLOG?
    define('SEGMENT_WC_ECOMMERCE', 0); //Ultra segmentar páginas do E-COMMERCE?
    define('SEGMENT_WC_IMOBI', 0); //Ultra segmentar páginas do imobi?
    define('SEGMENT_WC_EAD', 0); //Ultra segmentar páginas do EAD?
    define('SEGMENT_GL_ANALYTICS_UA', ''); //ID do Google Analytics (UA-00000000-0)
    define('SEGMENT_FB_PAGE_ID', ''); //ID do Facebook Pages (Obrigatório para POST - Instant Article)
    define('SEGMENT_GL_ADWORDS_ID', ''); //ID do pixel do Adwords (todo o site)
    define('SEGMENT_GL_ADWORDS_LABEL', ''); //Label do pixel do Adwords (todo o site)


    /*
     * APP LINKS
     * Habilitar ou desabilitar campos de links alternativos
     */
    define('APP_LINK_POSTS', 1); //Posts
    define('APP_LINK_PAGES', 1); //Páginas
    define('APP_LINK_PRODUCTS', 1); //Produtos
    define('APP_LINK_PROPERTIES', 1); //Imóveis

    /*
     * ACCOUNT CONFIG
     */
    define('ACC_MANAGER', 1); //Conta de usuários (UI)
    define('ACC_TAG', 'Minha Conta!'); //null para OLÁ {NAME} ou texto (Minha Conta, Meu Cadastro, etc)

    /*
     * COMMENT CONFIG
     */
    define('COMMENT_MODERATE', 1); //Todos os NOVOS comentários ficam ocultos até serem aprovados
    define('COMMENT_ON_POSTS', 1); //Aplica comentários aos posts
    define('COMMENT_ON_PAGES', 1); //Aplica comentários as páginas
    define('COMMENT_ON_PRODUCTS', 1); //Aplica comentários aos produtos
    define('COMMENT_SEND_EMAIL', 1); //Envia e-mails transicionais para usuários sobre comentários
    define('COMMENT_ORDER', 'DESC'); //Ordem de exibição dos comentários (ASC ou DESC)
    define('COMMENT_RESPONSE_ORDER', 'ASC'); //Ordem de exibição das respostas (ASC ou DESC)

    /*
     * ACTIVECAMPAIGN CONFIG
     */
    define('ACTIVE_CAMPAIGN', 1); //Ativa cadastro em newsletter
    define('ACTIVE_CAMPAIGN_URL', 'https://charmefitness1125.api-us1.com'); //URL da conta ActiveCampaign
    define('ACTIVE_CAMPAIGN_KEY', 'edae4cc3d54eed89806d385db428521a4100f687f40ee142bdff68cc407507117aa4850a'); //KEY da conta ActiveCampaign
    define('ACTIVE_CAMPAIGN_LISTS', '1'); //ID das listas separados por vírgula ('1' OU '1,2')
    define('ACTIVE_CAMPAIGN_TAGS', 'Leads'); //Tags separadas por vírgula ('WC' OU 'WC,Mentor')

    /*
     * ECOMMERCE CONFIG
     * IMPORTANTE EM E_ORDER_PAYDATE: Um tempo muito grande para pagamento pode implicar
     * em extender descontos expirados. Uma oferta pode acabar e o usuário ainda consegue
     * pagar neste prazo de dias!
     */
    define('E_PDT_LIMIT', 0); //Limite de produtos cadastrados. NULL = sem limite
    define('E_PDT_SIZE', 'default'); //Tamanho padrão para produtos!
    define('E_ORDER_DAYS', 1); //Dias para cancelar pedidos não pagos (Novo Pedido)
    define('ECOMMERCE_TAG', 'Minhas Compras'); //Meu Carrinho, Minha Cesta, Minhas Compras, Etc;
    define('ECOMMERCE_STOCK', 1); //true para controlar o estoque e false para não! (Ainda será nessesário alimentar o estoque para o carrinho)
    define('ECOMMERCE_BUTTON_TAG', 'Comprar Agora'); //Meu Carrinho, Minha Cesta, Minhas Compras, Etc;
    /*
     * Parcelamento
     */
    define('ECOMMERCE_PAY_SPLIT', 1); //Aceita pagamento parcelado?
    define('ECOMMERCE_PAY_SPLIT_MIN', 5); //Qual valor mínimo da parcela? (consultar método de pagamento)
    define('ECOMMERCE_PAY_SPLIT_NUM', 6); //Qual o número máximo de parcelas? (consultar método de pagamento)
    define('ECOMMERCE_PAY_SPLIT_ACM', 2.99); //Juros aplicados ao mês! (consultar método de pagamento)
    define('ECOMMERCE_PAY_SPLIT_ACN', 6); //Parcelas sem Juros (consultar método de pagamento)
    /*
     * abandono de carrinho
     */
    define('ECOMMERCE_ABANDONED_CART', 1); //Ativa abandono de carrinho
    define('ECOMMERCE_ABANDONED_CART_COUPON', 'EUVOLTEI'); //Cupom de desconto no e-mail

    /*
     * SHIPMENT CONFIG
     * 1. Frete gratuito a partir do valor X
     */
    define('ECOMMERCE_SHIPMENT_FREE', 169.99); //Opção de frete grátis a partir do valor X (Informe o valor ou false)
    define('ECOMMERCE_SHIPMENT_FREE_DAYS', 12); //Máximo de dias úteis para a entrega no frete gratuito!
    /*
     * Valor de frete fixo!
     */
    define('ECOMMERCE_SHIPMENT_FIXED', 0); //Oferecer frete com valor fixo?
    define('ECOMMERCE_SHIPMENT_FIXED_PRICE', 15.00); //Valor do frete fixo
    define('ECOMMERCE_SHIPMENT_FIXED_DAYS', 15); //Máximo de dias úteis para a entrega! 
    /*
     * Frete fixo por localidade!
     */
    define('ECOMMERCE_SHIPMENT_LOCAL', 0); //Entrega padrão para a Cidade (Ex: São Paulo, Florianópolis, false)
    define('ECOMMERCE_SHIPMENT_LOCAL_IN_PLACE', 1); //Permitir retirar na Loja?
    define('ECOMMERCE_SHIPMENT_LOCAL_PRICE', 5.00); //Taxa de entrega local! 
    define('ECOMMERCE_SHIPMENT_LOCAL_DAYS', 1); //Máximo de dias úteis para a entrega!
    /*
     * CIDADES FRETES GRATIS
     */
    define('ECOMMERCE_SHIPMENT_FREE_LOCAL', ''); //Cidades com frete grátis!
    define('ECOMMERCE_SHIPMENT_FREE_LOCAL_VALUE', 150); //Valor mínimo para frete grátis!
    define('ECOMMERCE_SHIPMENT_FREE_LOCAL_DAYS', 10); //Dias para frete grátis!
    /*
     * Frete por correios!
     */
    define('ECOMMERCE_SHIPMENT_CORREIOS_QUOTE', 1); //Cotar pelos Correios?
    define('ECOMMERCE_SHIPMENT_CORREIOS_CDEMPRESA', 0); //Usuário da empresa se tiver contrato com correios!
    define('ECOMMERCE_SHIPMENT_CORREIOS_CDSENHA', 0); //Senha da empresa se tiver contrato com correios!
    define('ECOMMERCE_SHIPMENT_CORREIOS_SERVICE', '04510,04014,40215,40290,40169'); //Tipos de serviços a serem consultados! (Consultar em Config.inc.php Função getShipmentTag())
    define('ECOMMERCE_SHIPMENT_CORREIOS_FORMAT', 1); //1 Caixa/Pacote, 2 Rolo/Bobina ou 3 Envelope?
    define('ECOMMERCE_SHIPMENT_CORREIOS_DECLARE', 0); //Declarar valor da compra para seguro?
    define('ECOMMERCE_SHIPMENT_CORREIOS_OWN_HAND', 'n'); //Postagem por mão própria? (s, n)
    define('ECOMMERCE_SHIPMENT_CORREIOS_BY_WEIGHT', 1); //Cálculo deduzido apenas por peso?
    define('ECOMMERCE_SHIPMENT_CORREIOS_ALERT', 0); //Aviso de recebimento?
    /*
     * Frete por transportadora
     */
    define('ECOMMERCE_SHIPMENT_COMPANY', 0); //Oferecer Transportadora?
    define('ECOMMERCE_SHIPMENT_COMPANY_VAL', 5); //Valor do frete por porcentagem do valor do pedido! (4% do valor do pedido)
    define('ECOMMERCE_SHIPMENT_COMPANY_PRICE', 30); //Valor mínimo para envio via transportadora. 100 = R$ 100
    define('ECOMMERCE_SHIPMENT_COMPANY_DAYS', 15); //Máximo de dias úteis para a entrega!
    define('ECOMMERCE_SHIPMENT_COMPANY_LINK', 'http://www.dhl.com.br/pt/express/rastreamento.html?AWB='); //Link para rastreamento (EX: http://www.dhl.com.br/pt/express/rastreamento.html?AWB=)
    /*
     * Frete pela transportadora TNT Mercúrio!
     */
    define('ECOMMERCE_SHIPMENT_TNT_QUOTE', 0); //Cotar pela TNT?
    define('ECOMMERCE_SHIPMENT_TNT_LOGIN', ''); //Usuário da empresa na TNT Mercúrio!
    define('ECOMMERCE_SHIPMENT_TNT_SENHA', ''); //Senha da empresa na TNT Mercúrio!
    define('ECOMMERCE_SHIPMENT_TNT_TPPESSOAREMETENTE', 'J'); //Tipos de pessoa do remetente (F para pessoa física ou J para pessoa jurídica)
    define('ECOMMERCE_SHIPMENT_TNT_TPSITUACAOTRIBUTARIAREMETENTE', 'ME'); //Tipos de situação tributária do remetente (Consultar em Config.inc.php Função getShipmentTntSitTrib())
    define('ECOMMERCE_SHIPMENT_TNT_CDDIVISAOCLIENTE', 2); //Código divisão do cliente (???)
    define('ECOMMERCE_SHIPMENT_TNT_TPFRETE', 'C'); //Tipos de fretes (C para CIF ou F para FOB)
    define('ECOMMERCE_SHIPMENT_TNT_TPSERVICO', 'RNC'); //Tipos de serviços a serem consultados (Consultar em Config.inc.php Função getShipmentTntTpServico())
    define('ECOMMERCE_SHIPMENT_TNT_BY_WEIGHT', 0); //Cálculo deduzido apenas por peso?
    /*
     * Frete pela transportadora Jamef!
     */
    define('ECOMMERCE_SHIPMENT_JAMEF_QUOTE', 0); //Cotar pela Jamef?
    define('ECOMMERCE_SHIPMENT_JAMEF_FILCOT', 22); //Filial de  coleta (Consultar em Config.inc.php Função getShipmentJamefFilCot())
    define('ECOMMERCE_SHIPMENT_JAMEF_SEGPROD', '000004'); //Segmentos de produtos (Consultar em Config.inc.php Função getShipmentJamefSegProd())
    define('ECOMMERCE_SHIPMENT_JAMEF_TIPTRA', 1); //Tipos de serviços a serem consultados (Consultar em Config.inc.php Função getShipmentJamefTipTra())
    define('ECOMMERCE_SHIPMENT_JAMEF_USUARIO', ''); //Usuário cadastrado na Jamef
    define('ECOMMERCE_SHIPMENT_JAMEF_BY_WEIGHT', 0); //Cálculo deduzido apenas por peso?
    /*
     * Frete pela transportadora JadLog!
     */
    define('ECOMMERCE_SHIPMENT_JADLOG_QUOTE', 0); //Cotar pela JadLog?
    define('ECOMMERCE_SHIPMENT_JADLOG_PASSWORD', ''); //Informar a senha de acesso à área de Serviços on-line do site da JADLOG
    define('ECOMMERCE_SHIPMENT_JADLOG_FRAP', 'N'); //Informar se Frete a pagar no destino, ?S? = sim ?N? = não.
    define('ECOMMERCE_SHIPMENT_JADLOG_TIPENTREGA', 'D'); //Informar o Tipo de entrega ?R? retira unidade JADLOG, ?D? domicilio.
    define('ECOMMERCE_SHIPMENT_JADLOG_VLCOLETA', 0.00); //Informar o valor da coleta negociado com representante JADLOG.
    define('ECOMMERCE_SHIPMENT_JADLOG_MODALIDADE', 4); //Informar a modalidade do frete. Deve conter apenas números (Consultar em Config.inc.php Função getShipmentJadlogModalidade())
    define('ECOMMERCE_SHIPMENT_JADLOG_SEGURO', 'N'); //Informar Tipo do Seguro ?N? normal ?A? apólice própria
    define('ECOMMERCE_SHIPMENT_JADLOG_BY_WEIGHT', 0); //Cálculo deduzido apenas por peso?
    define('ECOMMERCE_SHIPMENT_JADLOG_DAYS', '3 à 15'); //Informar manualmente o prazo de entrega Ex.: '3 à 15' ou 'até 15' ou '15'
    /*
     * Frete pela transportadora Tam Cargo!
     */
    define('ECOMMERCE_SHIPMENT_TAM_QUOTE', 0); //Cotar pela Tam?
    define('ECOMMERCE_SHIPMENT_TAM_USERNAME', ''); //Informar usuário de acesso ao MyCargo Manager da Tam Cargo
    define('ECOMMERCE_SHIPMENT_TAM_PASSWORD', ''); //Informar senha de acesso ao MyCargo Manager da Tam Cargo
    define('ECOMMERCE_SHIPMENT_TAM_PICKUP', 1); //Informar se deseja coleta
    define('ECOMMERCE_SHIPMENT_TAM_DELIVERY', 1); //Informar se deseja entrega
    define('ECOMMERCE_SHIPMENT_TAM_INSURANCE', 1); //Informar se deseja seguro
    define('ECOMMERCE_SHIPMENT_TAM_BY_WEIGHT', 0); //Cálculo deduzido apenas por peso?
    /*
     * Configurações adicionais de frete
     */
    define('ECOMMERCE_SHIPMENT_DELAY', 0); //Soma X dias ao prazo máximo de entrega do frete!
    define('ECOMMERCE_SHIPMENT_ADDITIONAL_PERCENT', 0); //Valor percentual a ser adicionado ao valor da cotaçao do frete!
    define('ECOMMERCE_SHIPMENT_ADDITIONAL_CHARGE', 0); //Valor fixo a ser adicionado ao valor da cotaçao do frete!

    /*
     * CONFIGURAÇÕES DE PAGAMENTO
     * É aconselhado criar um e-mail padrão para recebimento de pagamentos
     * como por exemplo pagamentos@site.com. E assim configurar todos os
     * meios de pagamentos nele. Para que o gestor da loja tenha acesso
     * as notificações de e-mail!
     */

    define('METHOD_PAYMENT', 'pagseguro'); //pagseguro, pagarme, cielo, moip

    /*
     * PAGSEGURO
     * ATENÇÃO: Para utilizar o checkout transparente é preciso habilitar a
     * conta junto ao PagSeguro. Para isso:
     *
     * Acesse: https://pagseguro.uol.com.br/receba-pagamentos.jhtml#checkout-transparent
     * Clique em Regras de uso - Uma modal abre!
     * Clique em entre em contato conosco. E informe os dados solicitados!
     */
    define('PAGSEGURO_ENV', 'sandbox'); //sandbox para teste e production para vender!
    define('PAGSEGURO_EMAIL', 'alissonpereira1993@gmail.com'); //E-mail do vendedor na pagseguro!
    define('PAGSEGURO_NOTIFICATION_EMAIL', 'pedidos@charmefitness.com.br'); //E-mail para receber notificações e gerenciar pedidos!

    /*
     * SANDBOX (AMBIENTE DE TESTE)
     */
    define('PAGSEGURO_TOKEN_SANDBOX', ''); //Token Sandbox (https://sandbox.pagseguro.uol.com.br/vendedor/configuracoes.html)
    define('PAGSEGURO_APP_ID_SANDBOX', ''); //Id do APP Sandbox (https://sandbox.pagseguro.uol.com.br/aplicacao/configuracoes.html)
    define('PAGSEGURO_APP_KEY_SANDBOX', ''); //Chave do AP Sandbox

    /*
     * PRODUCTION (AMBIENTE REAL)
     */
    define('PAGSEGURO_TOKEN_PRODUCTION', ''); //Token de produção (https://pagseguro.uol.com.br/preferencias/integracoes.jhtml)
    define('PAGSEGURO_APP_ID_PRODUCTION', ''); //Id do APP de integração (https://pagseguro.uol.com.br/aplicacao/listagem.jhtml)
    define('PAGSEGURO_APP_KEY_PRODUCTION', ''); //Chave do APP de integração!

    /*
     * PAGAR.ME
     */
    define('PAGARME_ENV', 'sandbox'); //sandbox para teste e production para vender!

    /*
     * SANDBOX (AMBIENTE DE TESTE)
     */
    define('PAGARME_API_KEY_SANDBOX', ''); //API key Sandbox

    /*
     * PRODUCTION (AMBIENTE REAL)
     */
    define('PAGARME_API_KEY_PRODUCTION', ''); //Api key Produção
    define('PAGARME_TX_JUROS', 13); //Consulte na Pagar.me
    define('PAGARME_TX_BILLET', 3.8); //Taxa por boleto confirmado (Consulte na Pagar.me) (3.8 = R$ 3,80)

    /*
     * CIELO
     */
    define('CIELO_ENV', 'sandbox'); //sandbox para teste e production para vender!
    define('CIELO_SOFT', 'RunFitness'); //Nome da loja impresso na fatura do cartão (sem espaços)
    define('CIELO_TX_BILLET', 4.2); //Taxa por boleto confirmado (Consulte na Cielo) (4.2 = R$ 4,20)
    define('CIELO_BILLET_MODE', 'BancodoBrasil'); //Brasdesco, BancodoBrasil = boletos NÃO registrados | Bradesco2, BancodoBrasil2 = boletos registrados

    /*
     * SANDBOX (AMBIENTE DE TESTE) Credênciais para teste: https://cadastrosandbox.cieloecommerce.cielo.com.br/
     */
    define('CIELO_MERCHANT_ID_SANDBOX', ''); //ID do merchant na Cielo Sandbox
    define('CIELO_MERCHANT_KEY_SANDBOX', ''); //KEY do merchant na Cielo Sandbox

    /*
     * PRODUCTION (AMBIENTE REAL)
     */
    define('CIELO_MERCHANT_ID_PRODUCTION', ''); //ID do merchant na Cielo Produção
    define('CIELO_MERCHANT_KEY_PRODUCTION', ''); //KEY do merchant na Cielo Produção

    /*
     * MOIP
     */
    define('MOIP_ENV', 'sandbox'); //sandbox para teste e production para vender!

    /*
     * SANDBOX (AMBIENTE DE TESTE)
     */
    define('MOIP_TOKEN_SANDBOX', ''); //Token Sandbox (https://conta-sandbox.moip.com.br/configurations/api_credentials)
    define('MOIP_KEY_SANDBOX', ''); //Key Sandbox (https://conta-sandbox.moip.com.br/configurations/api_credentials)

    /*
     * PRODUCTION (AMBIENTE REAL)
     */
    define('MOIP_TOKEN_PRODUCTION', ''); //Token Sandbox (https://conta-sandbox.moip.com.br/configurations/api_credentials)
    define('MOIP_KEY_PRODUCTION', ''); //Key Sandbox (https://conta-sandbox.moip.com.br/configurations/api_credentials)

    /*
     * CONFIGURAÇÕES DO EAD
     */
    define('EAD_REGISTER', 0); //Permitir cadastro na plataforma?
    define('EAD_HOTMART_EMAIL', 0); //Email de produtor hotmart!
    define('EAD_HOTMART_TOKEN', 0); //Token da API do hotmart!
    define('EAD_HOTMART_NEGATIVATE', 0); //Id de produtos na hotmart que NÃO serão entregues!
    define('EAD_HOTMART_LOG', 0); //Gerar Log de vendas?
    define('EAD_TASK_SUPPORT_DEFAULT', 1); //Por padrão habilitar suporte em todas as aulas?
    define('EAD_TASK_SUPPORT_EMAIL', "suporte@seusite.com.br"); //Enviar alertas de novos tickets para?
    define('EAD_TASK_SUPPORT_MODERATE', 0); //Tickets devem ser aprovados por um admin?
    define('EAD_TASK_SUPPORT_STUDENT_RESPONSE', 0); //Alunos podem responder o suporte?
    define('EAD_TASK_SUPPORT_PENDING_REVIEW', 0); //Tickets Pendentes de Avaliação.
    define('EAD_TASK_SUPPORT_REPLY_PUBLISH', 0); //Tickets Pendentes de Avaliação.
    define('EAD_TASK_SUPPORT_LEVEL_DELETE', 10); //Level mínimo para poder deletar tickets
    define('EAD_STUDENT_CERTIFICATION', 1); //Você pretende emitir certificados?
    define('EAD_STUDENT_MULTIPLE_LOGIN', 1); //Permitir login multiplo?
    define('EAD_STUDENT_MULTIPLE_LOGIN_BLOCK', 0); //Minutos de bloqueio quando login multiplo!
    define('EAD_STUDENT_CLASS_PERCENT', 100); //Assitir EAD_CLASS_PERCENT% para concluir!
    define('EAD_STUDENT_CLASS_AUTO_CHECK', 0); //Marcar tarefas como concluídas automaticamente?
endif;