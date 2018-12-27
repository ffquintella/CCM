
# Definições gerais sobre a API

## Versão 1.0
*Autor* - Felipe Quintella
*Contato* - ...@abc.com

## Pedidos HTTP permitidos

- **POST** - Para atulizar um rescurso
- **PUT** - Para adicionar um recurso a uma coleção (novo)
- **GET** - Parar ler um recurso ou uma lista de recursos
- **DELETE** - Para apagar um recurso


## Códigos de retorno implementados

 * 200 OK: successful request when data is returned
 * 201 Created: Successful request when something is created at another URL (specified by the value returned in the Location header)
 * 204 No Content: Successful request when no data is returned
 * 400 Bad Request: Incorrect parameters specified on request
 * 401 Authentication Required
 * 403 HTTPS Required
 * 404 Not Found: No resource at the specified URL
 * 405 Method Not Allowed: when a client makes a request using an HTTP verb not supported at the requested URL (supported verbs are returned in the Allow header)
 * 406 Not Acceptable: Requested data format not supported
 * 407 Format Invalid
 * 500 Internal Server Error: An unexpected error occurred
 * 501 Not Implemented: when a client makes a request using an unknown HTTP verb

## Por onde começar?

 Leia as seções relacionadas a autenticação e a listagem de recursos básicos. Também verifique que tipo de permissões você possui para acessar o sistema.

