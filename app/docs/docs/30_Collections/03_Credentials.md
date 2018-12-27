## api/credentials

Provêm acesso ao conjuto de configurations cadastrado

Versão: 1.1

---

### **GET** api/v1.1/credentials

Lista as databases que o usuário tem acesso

**Parâmetros do pedido**


| Parâmetro        |    Tipo       |  Obrigatório | Padrão | Descrição                                   |
|------------------|:-------------:|:------------:|--------|---------------------------------------------|
| format           |    string     |     Sim      | (xml/json) | Determina o retorno do pedido |
| app              |    string     |     No       | N/A        | Filtra pela app desejada |

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

