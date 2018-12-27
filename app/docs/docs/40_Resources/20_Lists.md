## api/lists/<<listName\>\>

Provêm acesso a uma lista específica

Versão: 1.1

---

### **GET** api/v1.1/lists/<<listName\>\>

Mostra os dados de uma lista específica

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



### **POST** api/v1.1/lists/<<listName\>\>

Atualiza uma lista existente. Ela precisa existir


**Códigos de resposta possíveis**


| Código   |    Descrição                                   |
|----------|------------------------------------------------|
| 200      |  Pedido executado com sucesso                  |
| 400      |  Pedido inválido  - Parâmetros mal formados    |
| 401      |  Autenticação necessária                       |
| 403      |  HTTPS necessário                              |
| 550      |  Erro interno                                  |


### **PUT** api/v1.1/lists/<<listName\>\>

Cria uma nova lista. Ela não pode existir


**Códigos de resposta possíveis**


| Código   |    Descrição                                   |
|----------|------------------------------------------------|
| 201      |  Pedido executado com sucesso                  |
| 400      |  Pedido inválido  - Parâmetros mal formados    |
| 401      |  Autenticação necessária                       |
| 403      |  HTTPS necessário                              |
| 550      |  Erro interno                                  |



### **DELETE** api/v1.1/lists/<<listName\>\>

APAGA uma lista.

**Códigos de resposta possíveis**


| Código   |    Descrição                                   |
|----------|------------------------------------------------|
| 201      |  Pedido executado com sucesso                  |
| 400      |  Pedido inválido  - Parâmetros mal formados    |
| 401      |  Autenticação necessária                       |
| 403      |  HTTPS necessário                              |
| 550      |  Erro interno                                  |

