# How to use the SolrXml record driver in VuFind

## install VuFind

* git clone ...
* git checkout v9.1.1
* php install.php
* Install/Home
* ./solr.sh start

## change sample record

```
curl -X POST -H 'Content-Type: application/json' 'http://localhost:8983/solr/biblio/update?commit=true' --data-binary '
[{
    "id":"testsample2",
    "fullrecord":{"set":"<document><record_format>xml</record_format><test>TEST</test></document>"},
    "record_format":{"set":"xml"}
}]'
```

## install files 

Install the provided files in the following paths:
* module/Bsb/src/RecordDriver/SolrXml.php
* module/Bsb/src/RecordDriver/SolrXmlFactory.php
* module/Bsb/Module.php
* module/Bsb/config/module.config.php
* local/config/vufind/fidxml.ini

## display example field

add the following to
themes/bootstrap3/templates/RecordDriver/DefaultRecord/result-list.phtml
line 136:
```
      <?php
        if ( get_class($this->driver) == 'Bsb\RecordDriver\SolrXml' ) {
          echo $this->driver->getFidField0('test');
        }
      ?>
```
