# Wizards Technologies' PHP REST API Library
A REST API Library based on fraktal to make your life easier.

## Doc flow
### Purpose and portability
TODO
### Most common use case, symfony + doctrine orm + annotations + jsonapi in 10 minutes
/!\ TODO
The serializer will transform your entities based on convention that you want to expose all the properties marked with the
@Exposable annotation. The library will then use the method get.ucfirst(propertyName) to retrieve it's value, or the getter name if specified in the annotation.

### Advanced docs: symfony guide, laravel guide, Plain Old Php
TODO

## Done
- serializing: jsonapi, array
- automatic filtering on doctrine orm collections with jsonapi params (filter, fields, sort, deep filter)
- pagination: pagerfanta


## Todo
- !tests
- ~sparse fieldset
- ~advanced filter operators such as like,in,between,
- ~eager fetch helper interface + orm
- ?serialization groups