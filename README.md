# amixml2array

Simples classe de conexão e comandos(actions) ami utilizando php e github.

Para habilitar , em manager.conf
Ver mais em https://wiki.asterisk.org/wiki/display/AST/Allow+Manager+Access+via+HTTP

```sh
enabled = yes 
webenabled = yes
```

Observar a config permit 

Ex liberar acesso para o ip 192.168.1.10

permit = 192.168.1.10/255.255.255.0

Usuário de exemplo em manager.conf

```sh
[teste_asterisk]
permit = 127.0.0.1/255.255.255.0
permit = 192.168.1.10/255.255.255.0
secret = asterisk
read = call,dialplan
write = system,all
```
Exemplos

```sh
$ami = new AmiXml2Array();

//Status dos sip peers
//json
print_r($ami->getSimpleAction('SIPpeers',true));
//array
print_r($ami->getSimpleAction('SIPpeers'));

//Status de todas as filas
//json
print_r($ami->getSimpleAction('QueueStatus',true));
//array
print_r($ami->getSimpleAction('QueueStatus'));

//Status da fila from fila_teste
//json
print_r($ami->getQueueStatusByName('fila_teste',true));
//array
print_r($ami->getQueueStatusByName('fila_teste'));

//Status do peer 11004
//json
print_r($ami->getSipStatusBySipPeer('11004',true));
//array
print_r($ami->getSipStatusBySipPeer('11004'));
```
