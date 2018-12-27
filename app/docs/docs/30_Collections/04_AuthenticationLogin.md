## api/authenticationLogin

Provêm acesso ao sistema de autenticação

Versão: 1.1

---

### **POST** api/v1.1/authenticationLogin

Lista as databases que o usuário tem acesso

**Parâmetros do pedido**

| Parâmetro  |    Tipo       |  Obrigatório | Padrão   | Descrição                                              |
|------------|:-------------:|:------------:|----------|--------------------------------------------------------|
| username   |    string     |     Sim      |          | Nome cadastrado do sistema                             |
| password   |    string     |     Sim      |          | Senha de acesso ao GCC                                 |
| type       |    string     |     Não      | user/sis | No caso de conflito de nomes define o tipo de usuário  |


**Códigos de resposta possíveis**


| Código   |    Descrição                                   |
|----------|------------------------------------------------|
| 200      |  Pedido executado com sucesso                  |
| 204      |  Usuário não encontrado                      |
| 400      |  Pedido inválido  - Parâmetros mal formados    |
| 401      |  Autenticação necessária                       |
| 403      |  HTTPS necessário                              |


**Exemplo:**

**Requisição**

    curl -k -u user:password  http://servidor/api/authenticationLogin

*PHP*

       //set POST variables

        $fields = array(
            'username' => 'user',
            'password' => 'password',
        );

        $fields_string = "";
        //url-ify the data for the POST
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');

        $this->ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($this->ch,CURLOPT_HEADER, false);
        curl_setopt($this->ch,CURLOPT_URL, $this->url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch,CURLOPT_POST, count($fields));
        curl_setopt($this->ch,CURLOPT_POSTFIELDS, $fields_string);

        //execute post
        $result = curl_exec($this->ch);


**Saída**

*json*

    { "userName":"user",
      "tokenType":"system",
      "tokenValue":"bypA4ALGrL5AgC1tEQu4gHRmIzwkvv+1wQoIPgwgNa2WAnHyUJQ5+LsCw"}

*xml*

    <token
        value="J62kOAq+fh4QHdkELQmDhqvg3wQoIPgwgNa2WAnHyUJQ5+LsCw"
        username="user"
        tokentype="system"/>