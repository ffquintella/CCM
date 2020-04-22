# Table of Contents

The CCM System helps you manage credentials and configurations filtering access to sepecific servers and environments. 

This is the CCM 2.0 version witch is a complete rewrite to dotnet core. All the tools clients where also rewritten. 

## Features 

### Version 2.0 

* Enviroment management
* Device management 
* Credential managing
* Logs & Audit
* WebUI
* RestAPI
* Docker publishing
* User, Groups & Roles Management

### Version 2.1

* Configuration managing
** Simple string configurations
** Puppet class configurations

### Version 2.2 
* Linux clients 
* Linux inventory 
* Linux password reset 

### Version 2.3
* Windows clients
* Windows inventory
* Windows password reset

### Version 2.4
* Integration with rdp and ssh clients for windows
* Integration with rdp and ssh clients for mac 
* Integration with rdp and ssh clients for linux


## Introduction

This system combines a password vault and a configuration management system. All free and opensource.


## Installation

The supported way to run the system is using docker. 

```bash
docker pull fquintella/CCM:<<version>>
```

## Quick Start

Just run the docker image it should start automatically


The first thing you need to do after starting a new instance is bootstrap it. This can be donne calling the bootstrap api. Or running the bootstraping script included. 

## Configuration

### Data Store

It is necessary to setup volumes for the ignite and fileStorage folders

### User

The default user created by the bootstrap script is called admin with password admin. 

**CHANGE IT!!!**

### Ports

The server runs on the following port

* `5001/tcp` - Porta padrão do servidor web


## Upgrade from previous version

Upgrading from the 1.5 to the 2.0 version is a complex procedure and should not be donne without help.

## Credits

This program was developed by:

- Felipe F. Quintella
