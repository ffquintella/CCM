## api/databases/<<databaseName\>\>

Provêm acesso a uma conta específica

Versão: v1.0

---

### **GET** api/VERSION/accounts/<<accountName\>\>

Mostra os dados de uma conta específica

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

    curl -k -u user:password  http://server/api/accounts/Utestes

*PHP*



**Saída**

*json*

     {
        "name": "Utestes",
        "permissions": {
            "admin": true
        }
     }



### **PUT** api/accounts/<<accountName\>\>

Cria uma conta.

OBS: No envio somente json é suportado.

**Body**
O campo body precisa conter um json com os seguintes dados

| Parâmetro        |    Tipo       |  Obrigatório | Padrão | Descrição                                   |
|------------------|:-------------:|:------------:|--------|---------------------------------------------|
|name              |    string     |     Sim      |  n/a   | Nome da conta - precisa ser único |
|permissions       |    hash       |     Não      |  n/a   | Hash de permissiões               |

**Permissions:**

Cada permissão contem uma chave identificadora e um valor que pode ser: true/false ou um qualificador


**Exemplo:**

    {"name": "Utestes", "permissions": { "admin": true } }


**Parâmetros de uri**

| Parâmetro        |    Tipo       |  Obrigatório | Padrão | Descrição                                   |
|------------------|:-------------:|:------------:|--------|---------------------------------------------|
| format           |    string     |     Sim      | (xml/json) | Determina o retorno do pedido |


**Códigos de resposta possíveis**


| Código   |    Descrição                                                            |
|----------|-------------------------------------------------------------------------|
| 200      |  Pedido executado com sucesso                                           |
| 204      |  Conteúdo não encontrado                                                |
| 400      |  Pedido inválido  - Parâmetros mal formados                             |
| 401      |  Autenticação necessária                                                |
| 403      |  HTTPS necessário                                                       |
| 409      |  Conflict - Dados já existem ou estão em conflito com regras existentes |
| 550      |  Permissão negada                                                       |

**Exemplo:**

**Requisição**

    curl -k -u user:password  http://server/api/accounts/Utestes

*PHP*



**Saída**

*json*


### **POST** api/accounts/<<accountName\>\>

Altera uma conta.

Para usar o POST a conta precisa existir préviamente.

OBS: No envio somente json é suportado.

**Body**
O campo body precisa conter um json com os seguintes dados

| Parâmetro        |    Tipo       |  Obrigatório | Padrão | Descrição                                   |
|------------------|:-------------:|:------------:|--------|---------------------------------------------|
|accountName              |    string     |     Sim      |  n/a   | Nome da conta - precisa ser único |
|permissions       |    hash       |     Não      |  n/a   | Hash de permissiões               |

**Permissions:**

Cada permissão contem uma chave identificadora e um valor que pode ser: true/false ou um qualificador


**Exemplo:**

    {"name": "Utestes", "permissions": { "admin": true }, "authorization": "local" }


**Parâmetros de uri**

| Parâmetro        |    Tipo       |  Obrigatório | Padrão | Descrição                                   |
|------------------|:-------------:|:------------:|--------|---------------------------------------------|
| format           |    string     |     Sim      | (xml/json) | Determina o retorno do pedido |


**Códigos de resposta possíveis**


| Código   |    Descrição                                                            |
|----------|-------------------------------------------------------------------------|
| 200      |  Pedido executado com sucesso                                           |
| 204      |  Conteúdo não encontrado                                                |
| 400      |  Pedido inválido  - Parâmetros mal formados                             |
| 401      |  Autenticação necessária                                                |
| 403      |  HTTPS necessário                                                       |
| 409      |  Conflict - Dados já existem ou estão em conflito com regras existentes |
| 550      |  Permissão negada                                                       |

**Exemplo:**

**Requisição**

    curl -k -u user:password  http://server/api/accounts/Utestes

*PHP*



**Saída**

*json*


### **DELETE** api/accounts/<<accountName\>\>

Apaga uma conta.



**Códigos de resposta possíveis**


| Código   |    Descrição                                                            |
|----------|-------------------------------------------------------------------------|
| 200      |  Pedido executado com sucesso                                           |
| 204      |  Conteúdo não encontrado                                                |
| 401      |  Autenticação necessária                                                |
| 403      |  HTTPS necessário                                                       |
| 409      |  Conflict - Dados já existem ou estão em conflito com regras existentes |
| 550      |  Permissão negada                                                       |
