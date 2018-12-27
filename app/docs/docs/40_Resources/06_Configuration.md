## api/configurations/<<configurationName\>\>

Provêm acesso a uma configuração específica

Versão: 1.0

---

### **GET** api/VERSION/configurations/<<configurationName\>\>

Mostra os dados de uma configuração específica

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

    curl -k -u user:password  http://server/api/configurations/conf1

*PHP*



**Saída**

*json*




### **PUT** api/configurations/<<configurationName\>\>

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


### **POST** api/configurations/<<configurationName\>\>

Altera uma configuração.

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


### **DELETE** api/configurations/<<configurationName\>\>

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
