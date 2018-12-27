## api/accounts

Provêm acesso ao conjuto de accounts cadastrado

Versão: 1.0

---

### **GET** api/accounts

Lista as databases que o usuário tem acesso

**Parâmetros do pedido**


| Parâmetro        |    Tipo       |  Obrigatório | Padrão | Descrição                                   |
|------------------|:-------------:|:------------:|--------|---------------------------------------------|
| format           |    string     |     Sim      | (xml/json) | Determina o retorno do pedido |

**Códigos de resposta possíveis**


| Código   |    Descrição                                   |
|----------|------------------------------------------------|
| 200      |  Pedido executado com sucesso                  |
| 204      |  Conteúdo não encontrado                       |
| 400      |  Pedido inválido  - Parâmetros mal formados    |
| 401      |  Autenticação necessária                       |
| 403      |  HTTPS necessário                              |
| 550      |  Permissão negada                              |

**Exemplo:**

**Requisição**

    curl -k -u user:password  http://server/api/accounts

    
*PHP*

    <?php

    // Get cURL resource
    $ch = curl_init();

    // Set url
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8033/api/accounts?format=json');

    // Set method
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    // Set options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Set headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Cookie: gccAuthToken=s7uh5wdttfSwIxCahw2iohjwQRA9aQaIh%2FjwW%2BXfaABLyLOwbC6FOwLNwJWw",
     ]
    );


    // Send the request & save response to $resp
    $resp = curl_exec($ch);

    if(!$resp) {
      die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
    } else {
      echo "Response HTTP Status Code : " . curl_getinfo($ch, CURLINFO_HTTP_CODE);
      echo "\nResponse HTTP Body : " . $resp;
    }

    // Close request to clear up some resources
    curl_close($ch);



**Saída**

*json*

    {
      "User-1": "Utestes2",
      "User-2": "Utestes"
    }