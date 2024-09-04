<?php

/**
 * ActiveCampaign: Classe de integração com o ActiveCampaign criada na aula
 * E-mail marketing com ActiveCampaign via API do UpInside Play mostrando
 * sincronismo de contatos.
 * 
 * Na aula é possível entender como criar outros metodos.
 * 
 * @author Robson V. Leite <robson@upinside.com.br>
 * @link https://www.upinside.com.br/ Saiba mais
 * @copyright (c) 2017, Robson V. Leite - UPINSIDE TECNOLOGIA
 */
class ActiveCampaign {

    private $acUrl;
    private $acKey;
    private $action;
    private $output;
    private $callback;
    private $params;

    public function __construct() {
        $this->acUrl = ACTIVE_CAMPAIGN_URL;
        $this->acKey = ACTIVE_CAMPAIGN_KEY;
        $this->output = "json";
    }

    /**
     * getByEmail: Busca um contato pelo e-mail no AC.
     * @param string $email e-mail que deseja consultar
     */
    public function getByEmail($email) {
        $this->action = "contact_view_email";
        $this->params = ["email" => $email];
        $this->get();
    }

    /**
     * addActive: Adiciona o contato como ativo a uma ou mais listas em seu AC
     * @param string $email E-mail do contato
     * @param array $listId Vetor com ID AC de listas que vai cadastrar o usuário. [1,5,7]
     * @param string $firstname Primeiro nome do contato
     * @param string $lastname Sobrenome do contato
     * @param string $comaTags Tags separadas por vírgula. Upinside, UpInside Play, ActiveCampaign
     */
    public function addActive($email, array $listId, $firstname = null, $lastname = null, $comaTags = null) {
        $this->action = "contact_sync";
        $this->params = [
            "first_name" => $firstname,
            "last_name" => $lastname,
            "email" => $email
        ];

        foreach ($listId AS $lists) {
            $this->params["p[{$lists}]"] = $lists;
            $this->params["status[{$lists}]"] = 1;
        }

        if (!empty($comaTags)) {
            $this->params['tags'] = $comaTags;
        }

        $this->post();
    }

    /**
     * tagAdd: Adiciona tags a um contato pelo e-mail
     * @param string $email Email do usuário que vai receber as tags
     * @param array $tags Tags em um array ['tag1', 'tag2']
     * @return object mensagem de sucesso ou não.
     */
    public function addTag($email, array $tags) {
        $this->action = 'contact_tag_add';
        $this->params['email'] = $email;

        $i = 0;
        foreach ($tags as $tag) {
            $i++;
            $this->params["tags[{$i}]"] = $tag;
        }

        $this->post();
    }

    /**
     * tagRemove: Remove tags de um contato pelo e-mail
     * @param string $email Email do usuário que vai perder as tags
     * @param array $tags Tags em um array ['tag1', 'tag2']
     * @return object mensagem de sucesso ou não.
     */
    public function removeTag($email, array $tags) {
        $this->action = 'contact_tag_remove';
        $this->params['email'] = $email;

        $i = 0;
        foreach ($tags as $tag) {
            $i++;
            $this->params["tags[{$i}]"] = $tag;
        }

        $this->post();
    }

    /**
     * getCallback: Retorna os dados de resposta da integração!
     * @return object Objeto de retorno do ActiveCampaign
     */
    public function getCallback() {
        return $this->callback;
    }

    /**
     * PRIVATE METHODS
     */

    /**
     * Efetua uma comunicação via HTTP GET
     */
    private function get() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "{$this->acUrl}/admin/api.php?api_key={$this->acKey}&api_action={$this->action}&api_output={$this->output}&" . http_build_query($this->params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        $this->callback = json_decode(curl_exec($ch));
        curl_close($ch);
    }

    /**
     * Efetua uma comunicação via HTTP POST
     */
    private function post() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "{$this->acUrl}/admin/api.php?api_key={$this->acKey}&api_action={$this->action}&api_output={$this->output}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->params);
        $this->callback = json_decode(curl_exec($ch));
        curl_close($ch);
    }

}
