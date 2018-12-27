# Table of Contents

The CCM System helps you manage credentials and configurations filtering access to sepecific servers and environments. 

This is the first public release and there is still a lot of working and translation to be done. 



## Supported tags

Current branch:

* 'latest'

For previous versions or newest releases see other branches.

## Introduction

Este é o sistema de Gestão de Configurações e Credenciais. Ele é uma evolução do antigo gubd.


O projeto contém um arquivo Docker para montar a imagem padrão.


### Version

* `XXXX` - Latest: Pequenos ajustes no código para padronizar para o refactoring


## Installation

A imagem deve ser obtida do repositório local da FGV. Procure o ESI para maiores informações.

```bash
docker pull XXXXXXXXX
```

Alternativamente a imagem pode ser construida localmente

```bash
git clone 
cd gcc
./build.sh
```

## Quick Start

Not written yet


## Configuration

### Data Store


### User

Sem usuários especiais para execução

### Ports

As seguintes portas ficam expostas na imagem

* `8000/tcp` - Porta padrão do servidor web



### Basic configuration using Environment Variables

> Some basic configurations are allowed to configure the system and make it easier to change at docker command line

- FACTER_XXXX -
- FACTER_PRE_RUN_CMD "" - Command to be executed just before starting bamboo
- FACTER_EXTRA_PACKS "" - Packages to be installed at runtime (must be centos7 packages on the defaul repos or epel)


## Upgrade from previous version


## Credits

Este programa foi desenvolvido pelas seguintes pessoas sob encomenda da FGV.

- Felipe F. Quintella
