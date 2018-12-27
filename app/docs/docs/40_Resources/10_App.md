## api/apps/<<appName\>\>

Provêm acesso a uma aplicação específica

Versão: 1.1

---

### **GET** api/v1.1/apps/<<appName\>\>

Mostra os dados de uma database específica

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



### **PUT** api/v1.1/apps/<<appName\>\>

Cria uma nova app

**Parâmetros do pedido**


| Parâmetro        |    Tipo       |  Obrigatório | Padrão     | Descrição                         |
|------------------|:-------------:|:------------:|------------|-----------------------------------|
| format           |    string     |     Sim      | (xml/json) | Determina o retorno do pedido     |


**Parâmetros do Body**

| Parâmetro        |    Tipo       |  Obrigatório | Padrão     | Descrição                                |
|------------------|:-------------:|:------------:|------------|------------------------------------------|
| environments     | list          |     Sim      |            | Lista dos ambientes que o app possui*    |
| key              | string        |     Não      |            | Chave de acesso - Tem tamanho minimo     |


* É validado com a lista Environments

**Exemplo de Body**

    {
      "environments": [
        "Produção",
        "Desenvolvimento"
      ],
      "key": "GCaZBvJnZgRfiSctgYmpBUzASXRRSZcx"
    }

**Códigos de resposta possíveis**


| Código   |    Descrição                                   |
|----------|------------------------------------------------|
| 200      |  Pedido executado com sucesso                  |
| 204      |  Conteúdo não encontrado                       |
| 400      |  Pedido inválido  - Parâmetros mal formados    |
| 401      |  Autenticação necessária                       |
| 403      |  HTTPS necessário                              |




### **POST** api/v1.1/apps/<<appName\>\>

Atualiza uma app

**Parâmetros do pedido**


| Parâmetro        |    Tipo       |  Obrigatório | Padrão | Descrição                         |
|------------------|:-------------:|:------------:|--------|-----------------------------------|
| format           |    string     |     Sim      | (xml/json) | Determina o retorno do pedido |

**Parâmetros do Body**

| Parâmetro        |    Tipo       |  Obrigatório | Padrão     | Descrição                                |
|------------------|:-------------:|:------------:|------------|------------------------------------------|
| environments     | list          |     Sim      |            | Lista dos ambientes que o app possui*    |
| key              | string        |     Não      |            | Chave de acesso - Tem tamanho minimo     |


* É validado com a lista Environments

**Exemplo de Body**

    {
      "environments": [
        "Produção",
        "Desenvolvimento"
      ],
      "key": "GCaZBvJnZgRfiSctgYmpBUzASXRRSZcx"
    }


**Códigos de resposta possíveis**


| Código   |    Descrição                                   |
|----------|------------------------------------------------|
| 200      |  Pedido executado com sucesso                  |
| 204      |  Conteúdo não encontrado                       |
| 400      |  Pedido inválido  - Parâmetros mal formados    |
| 401      |  Autenticação necessária                       |
| 403      |  HTTPS necessário                              |

### **DELETE** api/v1.1/apps/<<appName\>\>

Apaga um app

**Parâmetros do pedido**


| Parâmetro        |    Tipo       |  Obrigatório | Padrão | Descrição                         |
|------------------|:-------------:|:------------:|--------|-----------------------------------|
| format           |    string     |     Sim      | (xml/json) | Determina o retorno do pedido |


**Códigos de resposta possíveis**


| Código   |    Descrição                                   |
|----------|------------------------------------------------|
| 200      |  Pedido executado com sucesso                  |
| 204      |  Conteúdo não encontrado                       |
| 400      |  Pedido inválido  - Parâmetros mal formados    |
| 401      |  Autenticação necessária                       |
| 403      |  HTTPS necessário                              |



