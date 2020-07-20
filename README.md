## Prérequis
* docker
* docker-compose

## Initalization Docker
#### Build
`docker-compose build`

#### Up
`docker-compose up -d`

## Initalization Symfony
#### Install php dependances
`docker-compose exec php composer install`

#### Environnement
Create a file `.env.local` and copy/paste into your file this variables:

```
###> symfony/framework-bundle ###   
 APP_ENV=dev
 APP_SECRET=
 ###< symfony/framework-bundle ###
 
 ###> doctrine/doctrine-bundle ###
 DATABASE_URL=pgsql://postgres:root@postgres:5432/test_ldap
 ###< doctrine/doctrine-bundle ###
 
 ###> LDAP ###
 LDAP_HOST=localhost
 BASE_DN=dc=example,dc=org
 DN_ADMIN=cn=admin,dc=example,dc=org
 DN_PASSWORD=root
 LDAP_UID_KEY=uid
 ###< ENDLDAP ### 
```


## App access
#### Symfony app
`http://127.0.0.1:8080`

#### Postgres adminer
`http://127.0.0.1:8081`

#### Ldap admin
`http://127.0.0.1:8082`

* Login dn: cn=admin,dc=example,dc=org
* Password: root

