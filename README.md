Form listener for Symfony
========================

Easy form handling in Symfony controllers.

All you need is to use the @Form class annotation, for example:

`@Form("new_form",method="createCreateForm",starter="newAction",acceptor="createAction")`

The FormListener will handle the flow, it will create the form and inject it into the template parameters in `newAction` method. Also, it will handle the `createAction`; bind and validate the form. Only thing you have to do in `createAction` is to persist the entity.

You can also use the `rejector` property of `@Form` annotation to point a method which will be executed when form submission fails.

See [`PostController`](src/AppBundle/Controller/PostController.php) class for example how to use the `@Form` annotation.
