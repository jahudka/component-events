ComponentEvents
===============

[![Latest Stable Version](https://poser.pugx.org/jahudka/component-events/v)](https://packagist.org/packages/jahudka/component-events)
[![License](https://poser.pugx.org/jahudka/component-events/license)](https://packagist.org/packages/jahudka/component-events)
![Tests](https://github.com/jahudka/component-events/workflows/Tests/badge.svg)

This package provides a lazy bridge between various event dispatchers and the Nette Component model.
It integrates out of the box with `symfony/event-dispatcher`, `doctrine/event-manager` and
`contributte/nextras-orm-events`, but other projects can be easily added. The primary use case
of components reacting to events is, of course, redrawing snippets.

Why is this useful? Well, if you're developing an AJAX-first site and make heavy use of components
you'll run into situations where some business logic triggered from within one component should be
noticed by another, which might decide it needs to be redrawn. For example a shopping cart: if you
have a product list with an "Add to Cart" button then the product list is probably going to be 
composed of several components; the "Add to Cart" button might be one of them. If the user hits
the button, the button component handles that directly and so it can redraw itself as needed, but
how will the Cart component sitting in the top right of the page know that it should be redrawn as well?
When the signal from the button press is being handled by the Button component the Cart component might
not even be created yet. Enter ComponentEvents: make the Cart component an event subscriber and have
your business logic emit an event when the cart contents are changed. ComponentEvents will register
a relay listener for the event in the event dispatcher and when the event is emitted it will relay it
to the component, which will get lazily created at that moment if it wasn't created yet.

The package works by statically analysing all services implementing the `Nette\Application\IPresenter`
class when the DI container is being compiled. Since components are created using statically defined
`createComponent<Name>()` methods it is easy to traverse the component tree and check each
component for the relevant interfaces, provided the factory methods have appropriate return type
hints.

## Installation

You can install ComponentEvents using Composer:

```shell
composer require jahudka/component-events
```

Then you need to register the `Jahudka\ComponentEvents\ComponentEventsExtension` in your config.

## Integrations

### Symfony Event Dispatcher

Integration with `symfony/event-dispatcher` and its Nette wrapper `contributte/event-dispatcher`
works out of the box and you don't need to do anything special to use it. Simply implement the
`Symfony\Component\EventDispatcher\EventSubscriberInterface` in any component you wish and prosper.

### Doctrine Event Manager

Integration with `doctrine/event-manager`, as well as any wrapper which registers an instance of
the `Doctrine\Common\EventManager` class or its descendant in the DIC, should work _almost_ as well
as the Symfony integration. The only difference here is that unlike the EventSubscriber interface
in Symfony, the `getSubscribedEvents()` method in the `Doctrine\Common\EventSubscriber` interface
is not `static`, which means we can't _call_ it statically when we're analysing a class which
implements it during container rebuild. The ComponentEvents Doctrine bridge works around this
by creating an instance of the implementing class _without_ calling the constructor and then calling
the `getSubscribedEvents()` method of the instance - but this means that if the method tries to e.g.
access a dependency that should've been set in the constructor the call will fail. The Doctrine
bridge will simply ignore the component in that case.

### Nextras ORM Events

The `contributte/nextras-orm-events` package is by far the most obnoxious to use because, unlike
the previous two, this one _requires_ you to code something differently than you're used to.
Specifically, with traditional Nextras ORM Events you must specify _listeners_ using annotations
on the _entity class_, but with ComponentEvents you need to specify _entities_ using annotations
on the _listener class_. For example: let's say you have a Book entity and an Author entity.
The Book entity could have a traditional service attached as a listener like this:

```php
// Book.php

/**
 * @AfterInsert(App\Listener\NewBookListener)
 */
class Book extends Entity {}

// NewBookListener.php

class NewBookListener implements AfterInsertListener {
    public function onAfterInsert(IEntity $entity) : void { /* ... */ }
}
```

But the implementation of Nextras ORM Events means that the `NewBookListener` class
_must_ be registered as a service in the DIC and it will be created every time the
repository class configured for the Book entity is created. Contrast this with
a component configured as a listener for the same event using ComponentEvents:

```php
// Book.php

class Book extends Entity {}

// AuthorBookCountControl.php

/**
 * @AfterInsert(App\Entity\Book)
 */
class AuthorBookCountControl extends Control implements AfterInsertListener {
    public function onAfterInsert(IEntity $entity) : void {
        $this->redrawControl();
    }
}
```

It's about the same amount of code, only written elsewhere. Note that you can
specify multiple entity classes in the annotation as a comma-separated list,
and namespace resolution works exactly the same as native PHP namespaces,
so `use` statements are taken into consideration and specifying `App\Entity\Book`
in an annotation on a class that is itself in the `App\Components` namespace
will resolve to `App\Components\App\Entity\Book`, which is the
**only acceptable way it should ever be resolved** - if you want it to resolve
to `App\Entity\Book`, either `use` it and specify just `Book` in the annotation,
or prefix it with a backslash - just like you would in PHP code.
