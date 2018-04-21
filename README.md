# CreamIO Symfony Image Gallery Bundle

Service to handle image galleries built over [Symfony 4.0][3].

Requirements
------------

  * Symfony 4;
  * PHP 7.2 or higher;
  * Composer;
  * MySQL database;
  * PDO PHP extension;
  * creamio/symfony_basebundle (included in require);
  * creamio/symfony_uploadbundle (included in require);
  * and the [usual Symfony application requirements][1].
  
Installation
------------

Require the bundle from a symfony 4 application.

Add the routes to your application by adding to `config/routes.yaml` the following lines:

```yaml
_creamio_imagegallerybundle:
    resource: '@CreamIOImageGalleryBundle/Resources/config/routing.xml'
    prefix: /admin/api
```

Add the configuration for the upload bundle to your application by adding `config/packages/creamio_upload.yaml`:

```yaml
creamio_upload:
    upload_directory: '%kernel.project_dir%/public/uploads'
    default_upload_file_class: 'CreamIO\ImageGalleryBundle\Entity\GalleryImage'
    default_upload_file_field: 'file'
```

Project tree
------------

```bash
.
└── src
    ├── Controller           # API route controller
    ├── DependencyInjection
    ├── Entity               # Image and Category entities
    ├── Repository           # Image and Category repositories
    ├── Resources
    │   └── config           # Service injection and routes
    └── Service              # Gallery management service
```

License
-------
[![Creative Commons License](https://i.creativecommons.org/l/by-nc-sa/4.0/88x31.png)](http://creativecommons.org/licenses/by-nc-sa/4.0/)

This software is distributed under the terms of the Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International Public License. License is described below, you can find a human-readable summary of (and not a substitute for) the license [here](http://creativecommons.org/licenses/by-nc-sa/4.0/).


[1]: https://symfony.com/doc/current/reference/requirements.html
[2]: https://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html
[3]: https://symfony.com/