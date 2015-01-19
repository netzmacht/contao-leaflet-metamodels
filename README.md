
MetaModels layer for the Leaflet maps integration for Contao CMS
================================================================

[![Build Status](http://img.shields.io/travis/netzmacht/contao-leaflet-metamodels/master.svg?style=flat-square)](https://travis-ci.org/netzmacht/contao-leaflet-metamodels)
[![Version](http://img.shields.io/packagist/v/netzmacht/contao-leaflet-metamodels.svg?style=flat-square)](http://packagist.com/packages/netzmacht/contao-leaflet-metamodels)
[![License](http://img.shields.io/packagist/l/netzmacht/contao-leaflet-metamodels.svg?style=flat-square)](http://packagist.com/packages/netzmacht/contao-leaflet-metamodels)
[![Downloads](http://img.shields.io/packagist/dt/netzmacht/contao-leaflet-metamodels.svg?style=flat-square)](http://packagist.com/packages/netzmacht/contao-leaflet-metamodels)
[![Contao Community Alliance coding standard](http://img.shields.io/badge/cca-coding_standard-red.svg?style=flat-square)](https://github.com/contao-community-alliance/coding-standard)

Features
--------

This extension provides a [MetaModel](https://github.com/MetaModels) layer for the 
[Leaflet maps integration for Contao CMS](https://github.com/netzmacht/contao-leaflet-maps). 

Each layer intergrates one specific MetaModel into the Maps. For each layer multiple renderers can be defined:
 
 - Create a marker for the MetaModel item.
 - Reference to a layer, vector or marker defined in the Leaflet extension.
 - Load a [GeoJSON](http://geojson.org/) feature or feature collection from an MetaModel attribute.  
 - Load a [GeoJSON](http://geojson.org/) feature or feature collection from files defined in a MetaModel attribute. 

**Styling**
 - For markers a default icon can be selected. Besides an attribute can be used to define a custom marker icon.
 - For referenced layers, which are included as a copy *(standalone mode)* optional default styles and icons
   can be defined.
   
Requirements
------------

The Leaflet integration for the Contao CMS requires at least PHP 5.4. 
This extension only works for [MetaModels 2.0.x](https://github.com/MetaModels/core/tree/tng).

Install
-------

This extension can be installed using composer:

```
$ php composer.phar require netzmacht/contao-leaflet-metamodels:~1.0
```
