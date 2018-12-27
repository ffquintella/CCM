## api/credentials/<<credentialName\>\>

Provêm acesso a uma credencial específica

Versão: 1.1

---

### **GET** api/v1.1/credentials/<<credentialName\>\>

Mostra os dados de uma credencial específica

**Parâmetros do pedido**

| Parâmetro        |    Tipo       |  Obrigatório | Padrão     | Descrição                                     |
|------------------|:-------------:|:------------:|------------|-----------------------------------------------|
| format           |    string     |     Sim      | (xml/json) | Determina o retorno do pedido                 |
| displayValues    |    bool       |     Não      |  true     | Determina se as senhas do cofre serão exibidas|

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



*PHP*



**Saída**

*json*




### **PUT** api/v1.1/credentials/<<credentialName\>\>

Cria uma configuração.

OBS: No envio somente json é suportado.

**Body**
O campo body precisa conter um json com os seguintes dados

| Parâmetro        |    Tipo       |  Obrigatório | Padrão | Descrição                                   |
|------------------|:-------------:|:------------:|--------|---------------------------------------------|
|name              |    string     |     Sim      |  n/a   | Nome da conta - precisa ser único |


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


*PHP*


**Saída**

*json*


### **POST** api/v1.1/credentials/<<credentialName\>\>

Altera uma credencial.

Para usar o POST a conta precisa existir préviamente.

OBS: No envio somente json é suportado.

**Body**
O campo body precisa conter um json com os seguintes dados

| Parâmetro        |    Tipo       |  Obrigatório | Padrão | Descrição                                   |
|------------------|:-------------:|:------------:|--------|---------------------------------------------|


**Exemplo:**



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

*PHP*


**Saída**

*json*


### **DELETE** api/v1.1/credentials/<<credentialName\>\>

Apaga uma credencial.



**Códigos de resposta possíveis**


| Código   |    Descrição                                                            |
|----------|-------------------------------------------------------------------------|
| 200      |  Pedido executado com sucesso                                           |
| 204      |  Conteúdo não encontrado                                                |
| 401      |  Autenticação necessária                                                |
| 403      |  HTTPS necessário                                                       |
| 409      |  Conflict - Dados já existem ou estão em conflito com regras existentes |
| 550      |  Permissão negada                                                       |
