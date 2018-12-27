## api/lists

Provêm acesso ao conjuto de listas cadastrado

Versão: 1.0

---

### **GET** api/lists

Lista as listas que o usuário tem acesso

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


**Exemplo:**

**Requisição**

    curl -k -u user:password  http://server/api/apps


*PHP*



**Saída**

*json*



*xml*

