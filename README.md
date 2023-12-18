# Mezzio Navigation ViewHelper

[![Latest Stable Version](https://poser.pugx.org/mimmi20/mezzio-navigation-laminasviewrenderer/v/stable?format=flat-square)](https://packagist.org/packages/mimmi20/mezzio-navigation-laminasviewrenderer)
[![Latest Unstable Version](https://poser.pugx.org/mimmi20/mezzio-navigation-laminasviewrenderer/v/unstable?format=flat-square)](https://packagist.org/packages/mimmi20/mezzio-navigation-laminasviewrenderer)
[![License](https://poser.pugx.org/mimmi20/mezzio-navigation-laminasviewrenderer/license?format=flat-square)](https://packagist.org/packages/mimmi20/mezzio-navigation-laminasviewrenderer)

## Code Status

[![codecov](https://codecov.io/gh/mimmi20/mezzio-navigation-laminasviewrenderer/branch/master/graph/badge.svg)](https://codecov.io/gh/mimmi20/mezzio-navigation-laminasviewrenderer)
[![Average time to resolve an issue](https://isitmaintained.com/badge/resolution/mimmi20/mezzio-navigation-laminasviewrenderer.svg)](https://isitmaintained.com/project/mimmi20/mezzio-navigation-laminasviewrenderer "Average time to resolve an issue")
[![Percentage of issues still open](https://isitmaintained.com/badge/open/mimmi20/mezzio-navigation-laminasviewrenderer.svg)](https://isitmaintained.com/project/mimmi20/mezzio-navigation-laminasviewrenderer "Percentage of issues still open")

## Installation

Run

```shell
composer require mimmi20/mezzio-navigation-laminasviewrenderer
```

### Render the navigation


Calling the view helper for menus in your layout script:

```php
<!-- ... -->

<body>
    <?= $this->navigation('default')->menu() ?>
</body>
<!-- ... -->
```

## Using multiple navigations

Once the mezzio-navigation module is registered, you can create as many navigation
definitions as you wish, and the underlying factories will create navigation
containers automatically.

Add the container definitions to your configuration file, e.g.
`config/autoload/global.php`:

```php
<?php
return [
    // ...

    'navigation' => [

        // Navigation with name default
        'default' => [
            [
                'label' => 'Home',
                'route' => 'home',
            ],
            [
                'label' => 'Page #1',
                'route' => 'page-1',
                'pages' => [
                    [
                        'label' => 'Child #1',
                        'route' => 'page-1-child',
                    ],
                ],
            ],
            [
                'label' => 'Page #2',
                'route' => 'page-2',
            ],
        ],

        // Navigation with name special
        'special' => [
            [
                'label' => 'Special',
                'route' => 'special',
            ],
            [
                'label' => 'Special Page #2',
                'route' => 'special-2',
            ],
        ],

        // Navigation with name sitemap
        'sitemap' => [
            [
                'label' => 'Sitemap',
                'route' => 'sitemap',
            ],
            [
                'label' => 'Sitemap Page #2',
                'route' => 'sitemap-2',
            ],
        ],
    ],
    // ...
];
```

> ### Container names have a prefix
>
> There is one important point to know when using mezzio-navigation as a module:
> The name of the container in your view script **must** be prefixed with
> `Mezzio\Navigation\`, followed by the name of the configuration key.
> This helps ensure that no naming collisions occur with other services.

The following example demonstrates rendering the navigation menus for the named
`default`, `special`, and `sitemap` containers.

```php
<!-- ... -->

<body>
    <?= $this->navigation('Mimmi20\Mezzio\Navigation\Default')->menu() ?>

    <?= $this->navigation('Mimmi20\Navigation\Special')->menu() ?>

    <?= $this->navigation('Mimmi20\Navigation\Sitemap')->menu() ?>
</body>
<!-- ... -->
```

# View Helpers

The navigation helpers are used for rendering navigational elements from
[`Mezzio\Navigation\Navigation`](../containers.md) instances.

There are 5 built-in helpers:

- Breadcrumbs, used for rendering the path to the currently
  active page.
- Links, used for rendering navigational head links (e.g.
  `<link rel="next" href="..." />`).
- Menu, used for rendering menus.
- Sitemap, used for rendering sitemaps conforming to the
  [Sitemaps XML format](http://www.sitemaps.org/protocol.php).
- Navigation, used for proxying calls to other navigational
  helpers.

All built-in helpers implements the interface `Mezzio\Navigation\LaminasView\View\Helper\Navigation\ViewHelperInterface`, which
adds integration with
[laminas-acl](https://docs.laminas.dev/laminas-permissions-acl/) or [laminas-rbac](https://docs.laminas.dev/laminas-permissions-rbac/) and
[laminas-i18n](https://docs.laminas.dev/laminas-i18n/). This interface `Mimmi20\Mezzio\Navigation\Helper\Navigation\HelperInterface`, which
defines the following methods:

Method signature                                                       | Description
---------------------------------------------------------------------- | -----------
`getContainer() : null\|ContainerInterface`                             | Retrieve the current navigation container, if any.
`hasContainer() : bool`                                                | Is any navigation container currently registered?
`setContainer(ContainerInterface $container) : self`                    | Set a navigation container.
`getTranslator() : null\|Laminas\I18n\Translator\TranslatorInterface`     | Retrieve the current translator instance, if any.
`setTranslator(Laminas\I18n\Translator\TranslatorInterface`) : self`      | Set a translator instance to use with labels.
`hasTranslator() : bool`                                               | Is a translator instance present?
`isTranslatorEnabled() : bool`                                         | Should translation occur? To be `true`, both the flag enabling translation must be set, and a translator instance present.
`setTranslatorEnabled(bool $flag) : self`                              | Set the flag indicating whether or not translation should occur.
`getAcl() : null\|Laminas\Permissions\Acl\AclInterface`                   | Retrieve the current ACL instance, if any.
`setAcl(Laminas\Permissions\Acl\AclInterface $acl) : self`                | Set an ACL instance.
`hasAcl() : bool`                                                      | Whether or not an ACL instance is present.
`getRole() : null\|string|\Laminas\Permissions\Acl\Role\RoleInterface`    | Retrieve the current ACL role instance, if any.
`setRole(string\|Laminas\Permissions\Acl\Role\RoleInterface $acl) : self` | Set an ACL role instance.
`hasRole() : bool`                                                     | Whether or not an ACL role instance is present.
`getUseAcl() : bool`                                                   | Whether or not to use ACLs; both the flag must be enabled and an ACL instance present.
`setUseAcl(bool $flag) : self`                                         | Set the flag indicating whether or not to use ACLs.
`__toString()`                                                         | Cast the helper to a string value; relies on `render()`.
`render()`                                                             | Render the helper to a string.

In addition to the method stubs from the interface, the abstract class also
implements the following methods:

Method signature                                                             | Description
---------------------------------------------------------------------------- | -----------
`getIndent() : string`                                                       | Retrieve the indentation string to use; default is 4 spaces.
`setIndent(string\|int $indent) : self`                                      | Set the indentation to use. In the case of an integer, this indicates the number of spaces. Indentation can be specified for all but the `Sitemap` helper.
`getMinDepth() : int`                                                        | Retrieve the minimum depth a page must have to be included in output
`setMinDepth(null\|int $depth) : self`                                       | Set the minimum depth a page must have to be included in output; `null` means no minimum.
`getMaxDepth() : int`                                                        | Retrieve the maximum depth a page must have to be included in output
`setMaxDepth(null\|int $depth) : self`                                       | Set the maximum depth a page must have to be included in output; `null` means no maximum.
`getRenderInvisible() : bool`                                                | Retrieve the flag indicating whether or not to render items marked as invisible; defaults to `false`.
`setRenderInvisible(bool $flag) : self`                                      | Set the flag indicating whether or not to render items marked as invisible.
`__call() : mixed`                                                           | Proxy method calls to the registered container; this allows you to use the helper as if it were a navigation container. See [the example below](#proxying-calls-to-the-navigation-container).
`findActive(/* ... */) : array`                                              | Find the deepest active page in the container, using the arguments `ContainerInterface $container, int $minDepth = null, int $maxDepth = -1)`. If depths are not given, the method will use the values retrieved from `getMinDepth()` and `getMaxDepth()`. The deepest active page must be between `$minDepth` and `$maxDepth` inclusively. Returns an array containing the found page instance (key `page`) and the depth (key `depth`) at which the page was found.
`htmlify(PageInterface $page) : string`                                      | Renders an HTML `a` element based on the give page.
`accept(PageInterface $page, bool $recursive = true) : bool`                 | Determine if a page should be accepted when iterating containers. This method checks for page visibility and verifies that the helper's role is allowed access to the page's resource and privilege.

If a container is not explicitly set, the helper will create an empty
`Mezzio\Navigation\Navigation` container when calling `$helper->getContainer()`.

### Proxying calls to the navigation container

Navigation view helpers use the magic method `__call()` to proxy method calls to
the navigation container that is registered in the view helper.

```php
$this->navigation()->addPage([
    'type' => 'uri',
    'label' => 'New page',
]);
```

The call above will add a page to the container in the `Navigation` helper.

## Translation of labels and titles

The navigation helpers support translation of page labels and titles. You can
set a translator of type `Laminas\I18n\Translator\TranslatorInterface` in the
helper using `$helper->setTranslator($translator)`.

If you want to disable translation, use `$helper->setTranslatorEnabled(false)`.

The [proxy helper](navigation.md) will inject its own translator to the helper
it proxies to if the proxied helper doesn't already have a translator.

> ### Sitemaps do not use translation
>
> There is no translation in the sitemap helper, since there are no page labels
> or titles involved in an XML sitemap.

## Integration with ACL

All navigational view helpers support ACLs.  An object implementing
`Laminas\Permissions\Acl\AclInterface` can be assigned to a helper instance with
`$helper->setAcl($acl)`, and role with `$helper->setRole('member')` or
`$helper->setRole(new Laminas\Permissions\Acl\Role\GenericRole('member'))`. If an
ACL is used in the helper, the role in the helper must be allowed by the ACL to
access a page's `resource` and/or have the page's `privilege` for the page to be
included when rendering.

If a page is not accepted by ACL, any descendant page will also be excluded from
rendering.

The [proxy helper](navigation.md) will inject its own ACL and role to the helper
it proxies to if the proxied helper doesn't already have any.

The examples below all show how ACL affects rendering.

## Navigation setup used in examples

This example shows the setup of a navigation container for a fictional software company.

Notes on the setup:

- The domain for the site is `www.example.com`.
- Interesting page properties are marked with a comment.
- Unless otherwise is stated in other examples, the user is requesting the URL
  `http://www.example.com/products/server/faq/`, which translates to the page
  labeled `FAQ` under "Foo Server".
- The assumed ACL and router setup is shown below the container setup.

```php
use Mezzio\Navigation\Navigation;

/*
 * Navigation container

 * Each element in the array will be passed to
 * Mezzio\Navigation\Page\(new PageFactory())->factory() when constructing
 * the navigation container below.
 */
$pages = [
    [
        'label'      => 'Home',
        'title'      => 'Go Home',
        'module'     => 'default',
        'controller' => 'index',
        'action'     => 'index',
        'order'      => -100, // make sure home is the first page
    ],
    [
        'label'      => 'Special offer this week only!',
        'module'     => 'store',
        'controller' => 'offer',
        'action'     => 'amazing',
        'visible'    => false, // not visible
    ],
    [
        'label'      => 'Products',
        'module'     => 'products',
        'controller' => 'index',
        'action'     => 'index',
        'pages'      => [
            [
                'label'      => 'Foo Server',
                'module'     => 'products',
                'controller' => 'server',
                'action'     => 'index',
                'pages'      => [
                    [
                        'label'      => 'FAQ',
                        'module'     => 'products',
                        'controller' => 'server',
                        'action'     => 'faq',
                        'rel'        => [
                            'canonical' => 'http://www.example.com/?page=faq',
                            'alternate' => [
                                'module'     => 'products',
                                'controller' => 'server',
                                'action'     => 'faq',
                                'params'     => ['format' => 'xml'],
                            ],
                        ],
                    ],
                    [
                        'label'      => 'Editions',
                        'module'     => 'products',
                        'controller' => 'server',
                        'action'     => 'editions',
                    ],
                    [
                        'label'      => 'System Requirements',
                        'module'     => 'products',
                        'controller' => 'server',
                        'action'     => 'requirements',
                    ],
                ],
            ],
            [
                'label'      => 'Foo Studio',
                'module'     => 'products',
                'controller' => 'studio',
                'action'     => 'index',
                'pages'      => [
                    [
                        'label'      => 'Customer Stories',
                        'module'     => 'products',
                        'controller' => 'studio',
                        'action'     => 'customers',
                    ],
                    [
                        'label'      => 'Support',
                        'module'     => 'products',
                        'controller' => 'studio',
                        'action'     => 'support',
                    ],
                ],
            ],
        ],
    ],
    [
        'label'      => 'Company',
        'title'      => 'About us',
        'module'     => 'company',
        'controller' => 'about',
        'action'     => 'index',
        'pages'      => [
            [
                'label'      => 'Investor Relations',
                'module'     => 'company',
                'controller' => 'about',
                'action'     => 'investors',
            ],
            [
                'label'      => 'News',
                'class'      => 'rss', // class
                'module'     => 'company',
                'controller' => 'news',
                'action'     => 'index',
                'pages'      => [
                    [
                        'label'      => 'Press Releases',
                        'module'     => 'company',
                        'controller' => 'news',
                        'action'     => 'press',
                    ],
                    [
                        'label'      => 'Archive',
                        'route'      => 'archive', // route
                        'module'     => 'company',
                        'controller' => 'news',
                        'action'     => 'archive',
                    ],
                ],
            ],
        ],
    ],
    [
        'label'      => 'Community',
        'module'     => 'community',
        'controller' => 'index',
        'action'     => 'index',
        'pages'      => [
            [
                'label'      => 'My Account',
                'module'     => 'community',
                'controller' => 'account',
                'action'     => 'index',
                'resource'   => 'mvc:community.account', // resource
            ],
            [
                'label' => 'Forums',
                'uri'   => 'http://forums.example.com/',
                'class' => 'external', // class,
            ],
        ],
    ],
    [
        'label'      => 'Administration',
        'module'     => 'admin',
        'controller' => 'index',
        'action'     => 'index',
        'resource'   => 'mvc:admin', // resource
        'pages'      => [
            [
                'label'      => 'Write new article',
                'module'     => 'admin',
                'controller' => 'post',
                'action'     => 'write',
            ],
        ],
    ],
];

// Create container from array
$container = new Navigation();
$container->addPages($pages);

// Store the container in the proxy helper:
$view->plugin('navigation')->setContainer($container);

// ...or simply:
$view->navigation($container);
```

In addition to the container above, the following router setup is added to the
configuration file of the module, e.g. `module/MyModule/config/module.config.php`

```php
return [
    /* ... */
    'router' [
        'routes' => [
            'archive' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/archive/:year',
                    'defaults' => [
                        'module'     => 'company',
                        'controller' => 'news',
                        'action'     => 'archive',
                        'year'       => (int) date('Y') - 1,
                    ],
                    'constraints' => [
                        'year' => '\d+',
                    ],
                ],
            ],
            /* You can have other routes here... */
        ],
    ],
    /* ... */
];
```

The setup of ACL can be done in a module class, e.g.
`module/MyModule/Module.php`:

```php
namespace MyModule;

use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Role\GenericRole;
use Laminas\Permissions\Acl\Resource\GenericResource;

class Module
{
    /* ... */
    public function getViewHelperConfig()
    {
        return [
            'factories' => [
                // This will overwrite the native navigation helper
                'navigation' => function(ViewHelperPluginManager $pm) {
                    // Setup ACL:
                    $acl = new Acl();
                    $acl->addRole(new GenericRole('member'));
                    $acl->addRole(new GenericRole('admin'));
                    $acl->addResource(new GenericResource('mvc:admin'));
                    $acl->addResource(new GenericResource('mvc:community.account'));
                    $acl->allow('member', 'mvc:community.account');
                    $acl->allow('admin', null);

                    // Get an instance of the proxy helper
                    $navigation = $pm->get('Mezzio\Navigation\Helper\Navigation');

                    // Store ACL and role in the proxy helper:
                    $navigation->setAcl($acl);
                    $navigation->setRole('member');

                    // Return the new navigation helper instance
                    return $navigation;
                }
            ]
        ];
    }
    /* ... */
}
```

# Navigation Proxy

The `navigation()` helper is a proxy helper that relays calls to other
navigational helpers. It can be considered an entry point to all
navigation-related view tasks.

The `Navigation` helper finds other helpers that implement
`Mezzio\Navigation\Helper\Navigation\HelperInterface`, which means custom view helpers
can also be proxied.  This would, however, require that the custom helper path
is added to the view.

When proxying to other helpers, the `Navigation` helper can inject its
container, ACL and optionally role, and a translator. This means that you won't
have to explicitly set all three in all navigational helpers, nor resort to
injecting by means of static methods.

## Methods

Method signature                                                               | Description
------------------------------------------------------------------------------ | -----------
`findHelper(string $helper, bool $strict = true) : Navigation\HelperInterface` | Finds the given helper, verifies that it is a navigational helper, and injects the current container, ACL and role instances,  and translator, if present. If `$strict` is `true`, the method will raise an exception when unable to find a valid helper.
`getInjectContainer() : bool`                                                  | Retrieve the flag indicating whether or not to inject the current container into proxied helpers; default is `true`.
`setInjectContainer(bool $flag) : self`                                        | Set the flag indicating whether or not to inject the current container into proxied helpers.
`getInjectAcl() : bool`                                                        | Retrieve the flag indicating whether or not to inject ACL and role instances into proxied helpers; default is `true`.
`setInjectAcl(bool $flag) : self`                                              | Set the flag indicating whether or not to inject ACL and role instances into proxied helpers.
`getInjectTranslator() : bool`                                                 | Retrieve the flag indicating whether or not to inject the current translator instance into proxied helpers; default is `true`.
`setInjectTranslator(bool $flag) : self`                                       | Set the flag indicating whether or not to inject the current translator instance into proxied helpers.
`getDefaultProxy() : string`                                                   | Retrieve the default proxy helper to delegate to when rendering; defaults to `menu`.
`setDefaultProxy(string $helper) : self`                                       | Set the default proxy helper to delegate to when rendering.
`render(ContainerInterface = null)`                                             | Proxies to the render method of the default proxy.


# Breadcrumbs

Breadcrumbs are used for indicating where in a sitemap a user is currently browsing, and are
typically rendered like the following:

```text
You are here: Home > Products > FantasticProduct 1.0
```

The `breadcrumbs()` helper follows the [Breadcrumbs Pattern](http://developer.yahoo.com/ypatterns/pattern.php?pattern=breadcrumbs)
as outlined in the Yahoo! Design Pattern Library, and allows simple
customization (minimum/maximum depth, indentation, separator, and whether the
last element should be linked), or rendering using a partial view script.

The Breadcrumbs helper finds the deepest active page in a navigation container,
and renders an upwards path to the root. For MVC pages, the "activeness" of a
page is determined by inspecting the request object, as stated in the section on
[MVC pages](#mvc-pages).

The helper sets the `minDepth` property to 1 by default, meaning breadcrumbs
will not be rendered if the deepest active page is a root page. If `maxDepth` is
specified, the helper will stop rendering when at the specified depth (e.g. stop
at level 2 even if the deepest active page is on level 3).

Methods in the breadcrumbs helper:

Method signature                            | Description
------------------------------------------- | -----------
`getSeparator() : string`                   | Retrieves the separator string to use between breadcrumbs; default is ` &gt; `.
`setSeparator(string $separator) : self`    | Set the separator string to use between breadcrumbs.
`getLinkLast() : bool`                      | Retrieve the flag indicating whether the last breadcrumb should be rendered as an anchor; defaults to `false`.
`setLinkLast(bool $flag) : self`            | Set the flag indicating whether the last breadcrumb should be rendered as an anchor.
`getPartial() : string\|array`              | Retrieve a partial view script that should be used for rendering breadcrumbs. If a partial view script is set, the helper's `render()` method will use the `renderPartial()` method. The helper expects the partial to be a `string` or an `array` with two elements. If the partial is a `string`, it denotes the name of the partial script to use. If it is an `array`, the first element will be used as the name of the partial view script, and the second element is the module where the script is found.
`setPartial(string\|array $partial) : self` | Set the partial view script to use when rendering breadcrumbs; see `getPartial()` for acceptable values.
`renderStraight()`                          | The default render method used when no partial view script is present.
`renderPartial()`                           | Used for rendering using a partial view script.

## Basic usage

This example shows how to render breadcrumbs with default settings.

In a view script or layout:

```php
<?= $this->navigation()->breadcrumbs(); ?>
```

The call above takes advantage of the magic `__toString()` method, and is
equivalent to:

```php
<?= $this->navigation()->breadcrumbs()->render(); ?>
```

Output:

```html
<a href="/products">Products</a> &gt; <a href="/products/server">Foo Server</a> &gt; FAQ
```

## Specifying indentation

This example shows how to render breadcrumbs with initial indentation.

Rendering with 8 spaces indentation:

```php
<?= $this->navigation()->breadcrumbs()->setIndent(8) ?>
```

Output:

```html
        <a href="/products">Products</a> &gt; <a href="/products/server">Foo Server</a> &gt; FAQ
```

## Customize output

This example shows how to customize breadcrumbs output by specifying multiple options.

In a view script or layout:

```php
<?= $this->navigation()->breadcrumbs()
    ->setLinkLast(true)                   // link last page
    ->setMaxDepth(1)                      // stop at level 1
    ->setSeparator(' ▶' . PHP_EOL);       // cool separator with newline
?>
```

Output:

```html
<a href="/products">Products</a> ▶
<a href="/products/server">Foo Server</a>
```

Setting minimum depth required to render breadcrumbs:

```php
<?= $this->navigation()->breadcrumbs()->setMinDepth(10) ?>
```

Output: Nothing, because the deepest active page is not at level 10 or deeper.

## Rendering using a partial view script

This example shows how to render customized breadcrumbs using a partial vew
script. By calling `setPartial()`, you can specify a partial view script that
will be used when calling `render()`.  When a partial is specified, the
`renderPartial()` method will be called when emitting the breadcrumbs. This
method will find the deepest active page and pass an array of pages that leads
to the active page to the partial view script.

In a layout:

```php
echo $this->navigation()->breadcrumbs()
    ->setPartial('my-module/partials/breadcrumbs');
```

Contents of `module/MyModule/view/my-module/partials/breadcrumbs.phtml`:

```php
<?= implode(', ', array_map(function ($a) {
  return $a->getLabel();
}, $this->pages)); ?>
```

Output:

```html
Products, Foo Server, FAQ
```

# Links

The `links()` helper is used for rendering HTML `LINK` elements. Links are used for describing
document relationships of the currently active page. Read more about links and
link types at:

- [Document relationships: the LINK element (HTML4 W3C Rec.)](http://www.w3.org/TR/html4/struct/links.html#h-12.3)
- [Link types (HTML4 W3C Rec.)](http://www.w3.org/TR/html4/types.html#h-6.12)

There are two types of relations; forward and reverse, indicated by the kewyords
`rel` and `rev`. Most methods in the helper will take a `$rel` param, which must
be either `rel` or `rev`. Most methods also take a `$type` param, which is used
for specifying the link type (e.g. `alternate`, `start`, `next`, `prev`,
`chapter`, etc).

Relationships can be added to page objects manually, or found by traversing the
container registered in the helper. The method `findRelation($page, $rel,
$type)` will first try to find the given `$rel` of `$type` from the `$page` by
calling `$page>findRel($type)` or `$page>findRel($type)`. If the `$page` has a
relation that can be converted to a page instance, that relation will be used.
If the `$page` instance doesn't have the specified `$type`, the helper will look
for a method in the helper named `search$rel$type` (e.g. `searchRelNext()` or
`searchRevAlternate()`). If such a method exists, it will be used for
determining the `$page`'s relation by traversing the container.

Not all relations can be determined by traversing the container. These are the
relations that will be found by searching:

- `searchRelStart()`, forward `start` relation: the first page in the container.
- `searchRelNext()`, forward `next` relation; finds the next page in the
  container, i.e. the page after the active page.
- `searchRelPrev()`, forward `prev` relation; finds the previous page, i.e. the
  page before the active page.
- `searchRelChapter()`, forward `chapter` relations; finds all pages on level 0
  except the `start` relation or the active page if it's on level 0.
- `searchRelSection()`, forward `section` relations; finds all child pages of
  the active page if the active page is on level 0 (a `chapter`).
- `searchRelSubsection()`, forward `subsection` relations; finds all child pages
  of the active page if the active pages is on level 1 (a `section`).
- `searchRevSection()`, reverse `section` relation; finds the parent of the
  active page if the active page is on level 1 (a `section`).
- `searchRevSubsection()`, reverse `subsection` relation; finds the parent of
  the active page if the active page is on level 2 (a `subsection`).

> ### Allowed relation types
>
> When looking for relations in the page instance (`$page->getRel($type)` or
> `$page->getRev($type)`), the helper accepts the values of type `string`,
`array`, `Traversable`,
> or `Mezzio\Navigation\Page\PageInterface`:
>
> - `PageInterface` instances are used directly.
> - If a string is found, it will be converted to a `Mezzio\Navigation\Page\Uri`.
> - If an array or `Traversable` is found, it will be converted to one or
>   several page instances. If the first key is numeric, it will be considered to
>   contain several pages, and each element will be passed to the [page
>   factory](../pages.md#creating-pages-using-the-page-factory).  If the first key
>   is not numeric, the value will be passed to the page factory directly, and a
>   single page will be returned.

The helper also supports magic methods for finding relations. E.g. to find
forward alternate relations, call `$helper->findRelAlternate($page)`, and to
find reverse section relations, call `$helper->findRevSection($page)`. Those
calls correspond to `$helper->findRelation($page, 'rel', 'alternate')` and
`$helper->findRelation($page, 'rev', 'section')`, respectively.

To customize which relations should be rendered, the helper uses a render flag.
The render flag is an integer value, and will be used in a
[bitwise and (`&`) operation](http://php.net/language.operators.bitwise) against
the helper's render constants to determine if the relation that belongs to the
render constant should be rendered.

See the example below for more information.

The `LinksInterface` helper defines the following constants:

- `Mezzio\Navigation\Helper\Navigation\LinksInterface::RENDER_ALTERNATE`
- `Mezzio\Navigation\Helper\Navigation\LinksInterface::RENDER_STYLESHEET`
- `Mezzio\Navigation\Helper\Navigation\LinksInterface::RENDER_START`
- `Mezzio\Navigation\Helper\Navigation\LinksInterface::RENDER_NEXT`
- `Mezzio\Navigation\Helper\Navigation\LinksInterface::RENDER_PREV`
- `Mezzio\Navigation\Helper\Navigation\LinksInterface::RENDER_CONTENTS`
- `Mezzio\Navigation\Helper\Navigation\LinksInterface::RENDER_INDEX`
- `Mezzio\Navigation\Helper\Navigation\LinksInterface::RENDER_GLOSSARY`
- `Mezzio\Navigation\Helper\Navigation\LinksInterface::RENDER_COPYRIGHT`
- `Mezzio\Navigation\Helper\Navigation\LinksInterface::RENDER_CHAPTER`
- `Mezzio\Navigation\Helper\Navigation\LinksInterface::RENDER_SECTION`
- `Mezzio\Navigation\Helper\Navigation\LinksInterface::RENDER_SUBSECTION`
- `Mezzio\Navigation\Helper\Navigation\LinksInterface::RENDER_APPENDIX`
- `Mezzio\Navigation\Helper\Navigation\LinksInterface::RENDER_HELP`
- `Mezzio\Navigation\Helper\Navigation\LinksInterface::RENDER_BOOKMARK`
- `Mezzio\Navigation\Helper\Navigation\LinksInterface::RENDER_CUSTOM`
- `Mezzio\Navigation\Helper\Navigation\LinksInterface::RENDER_ALL`

The constants from `RENDER_ALTERNATE` to `RENDER_BOOKMARK` denote standard HTML
link types.  `RENDER_CUSTOM` denotes non-standard relations specified in pages.
`RENDER_ALL` denotes standard and non-standard relations.

Methods in the links helper:

Method signature                                                                          | Description
----------------------------------------------------------------------------------------- | -----------
`getRenderFlag() : int`                                                                   | Retrieves the render flag; default is `RENDER_ALL`.
`setRenderFlag(int $flag) : self`                                                         | Set the render flag; see examples below.
`findAllRelations(PageInterface $page, int $flag = null) : array`                          | Finds all relations of all types for a given page.
`findRelation(PageInterface $page, string $rel, string $type) : PageInterface\|array\|null` | Finds all relations of a given type from a given page.
`searchRel*(PageInterface $page) : PageInterface\|null`                                     | Traverses a container to find forward relations to the `Start` page, the `Next` page, the `Prev`ious page, `Chapter`s, `Section`s, and `Subsection`s.
`searchRev*(PageInterface $page) : PageInterface\|null`                                     | Traverses a container to find reverse relations to `Section`s or `Subsection`s.
`renderLink(PageInterface $page, string $attrib, string $relation) : string`               | Renders a single `link` element.

## Basic usage

### Specify relations in pages

This example shows how to specify relations in pages.

```php
use Laminas\Config\Config;
use Mezzio\Navigation\Navigation;
use Mezzio\Navigation\Page\PageInterface;

$container = new Navigation([
    [
        'label' => 'Relations using strings',
        'rel'   => [
            'alternate' => 'http://www.example.org/',
        ],
        'rev'   => [
            'alternate' => 'http://www.example.net/',
        ],
    ],
    [
        'label' => 'Relations using arrays',
        'rel'   => [
            'alternate' => [
                'label' => 'Example.org',
                'uri'   => 'http://www.example.org/',
            ],
        ],
    ],
    [
        'label' => 'Relations using configs',
        'rel'   => [
            'alternate' => new Config([
                'label' => 'Example.org',
                'uri'   => 'http://www.example.org/',
            ]),
        ],
    ],
    [
        'label' => 'Relations using pages instance',
        'rel'   => [
            'alternate' => (new PageFactory())->factory([
                'label' => 'Example.org',
                'uri'   => 'http://www.example.org/',
            ]),
        ],
    ],
]);
```

### Default rendering of links

This example shows how to render a menu from a container registered in the
view helper.

In a view script or layout:

```php
<?= $this->navigation()->links() ?>
```

Output:

```html
<link rel="alternate" href="/products/server/faq/format/xml">
<link rel="start" href="/" title="Home">
<link rel="next" href="/products/server/editions" title="Editions">
<link rel="prev" href="/products/server" title="Foo Server">
<link rel="chapter" href="/products" title="Products">
<link rel="chapter" href="/company/about" title="Company">
<link rel="chapter" href="/community" title="Community">
<link rel="canonical" href="http://www.example.com/?page=server-faq">
<link rev="subsection" href="/products/server" title="Foo Server">
```

### Specify which relations to render

This example shows how to specify which relations to find and render.

Render only start, next, and prev:

```php
use Mezzio\Navigation\Helper\Navigation\LinksInterface;

$links = $this->navigation()->links();
$links->setRenderFlag(LinksInterface::RENDER_START | LinksInterface::RENDER_NEXT | LinksInterface::RENDER_PREV);
echo $links;
```

Output:

```html
<link rel="start" href="/" title="Home">
<link rel="next" href="/products/server/editions" title="Editions">
<link rel="prev" href="/products/server" title="Foo Server">
```

Render only native link types:

```php
$links->setRenderFlag(LinksInterface::RENDER_ALL ^ LinksInterface::RENDER_CUSTOM);
echo $links;
```

Output:

```html
<link rel="alternate" href="/products/server/faq/format/xml">
<link rel="start" href="/" title="Home">
<link rel="next" href="/products/server/editions" title="Editions">
<link rel="prev" href="/products/server" title="Foo Server">
<link rel="chapter" href="/products" title="Products">
<link rel="chapter" href="/company/about" title="Company">
<link rel="chapter" href="/community" title="Community">
<link rev="subsection" href="/products/server" title="Foo Server">
```

Render all but chapters:

```php
$links->setRenderFlag(Links::RENDER_ALL ^ Links::RENDER_CHAPTER);
echo $links;
```

Output:

```html
<link rel="alternate" href="/products/server/faq/format/xml">
<link rel="start" href="/" title="Home">
<link rel="next" href="/products/server/editions" title="Editions">
<link rel="prev" href="/products/server" title="Foo Server">
<link rel="canonical" href="http://www.example.com/?page=server-faq">
<link rev="subsection" href="/products/server" title="Foo Server">
```

# Menu

The `menu()` helper is used for rendering menus from navigation containers. By
default, the menu will be rendered using HTML `UL` and `LI` tags, but the helper
also allows using a partial view script.

Methods in the Menu helper:

Method signature                                                                            | Description
------------------------------------------------------------------------------------------- | -----------
`getUlClass() : string`                                                                     | Retrieve the CSS class used when rendering `ul` elements in `renderMenu()`.
`setUlClass(string $class) : self`                                                          | Set the CSS class to use when rendering `ul` elements in `renderMenu()`.
`getOnlyActiveBranch() : bool`                                                              | Retrieve the flag specifying whether or not to render only the active branch of a container.
`setOnlyActiveBranch(bool $flag) : self`                                                    | Set the flag specifying whether or not to render only the active branch of a container.
`getRenderParents() : bool`                                                                 | Retrieve the flag specifying whether or not to render parent pages when rendering the active branch of a container.
`setRenderParents(bool $flag) : self`                                                       | Set the flag specifying whether or not to render parent pages when rendering the active branch of a container. When set to `false`, only the deepest active menu will be rendered.
`getPartial() : string|array`                                                               | Retrieve a partial view script that should be used for rendering breadcrumbs. If a partial view script is set, the helper's `render()` method will use the `renderPartial()` method. The helper expects the partial to be a `string` or an `array` with two elements. If the partial is a `string`, it denotes the name of the partial script to use. If it is an `array`, the first element will be used as the name of the partial view script, and the second element is the module where the script is found.
`setPartial(string|array $partial) : self`                                                  | Set the partial view script to use when rendering breadcrumbs; see `getPartial()` for acceptable values.
`htmlify(/* ... */) : string`                                                               | Overrides the method from the abstract class, with the argument list `PageInterface $page, bool $escapeLabel = true, bool $addClassToListItem = false`. Returns `span` elements if the page has no `href`.
`renderMenu(ContainerInterface $container = null, $options = []) : string`                   | Default rendering method; renders a container as an HTML `UL` list. If `$container` is not given, the container registered in the helper will be rendered.  `$options` is used for overriding options specified temporarily without resetting the values in the helper instance; if none are set, those already provided to the helper will be used. Options are an associative array where each key corresponds to an option in the helper. See the table below for recognized options.
`renderPartial(ContainerInterface $container = null, string|array $partial = null) : string` | Used for rendering the menu using a partial view script.
`renderSubMenu(/* ... */) : string`                                                         | Renders the deepest menu level of a container's active branch. Accepts the arguments `ContainerInterface $container`, `string $ulClass = null`, `string|int $indent = null` (an integer value indicates number of spaces to use), `string $liActiveClass = null`.

The following are options recognized by the `renderMenu()` method:

Option name        | Description
------------------ | -----------
`indent`           | Indentation. Expects a `string` or an `int` value.
`minDepth`         | Minimum depth. Expects an `int` or `null` (no minimum depth).
`maxDepth`         | Maximum depth. Expects an `int` or `null` (no maximum depth).
`ulClass`          | CSS class for `ul` element. Expects a `string`.
`onlyActiveBranch` | Whether only active branch should be rendered. Expects a `boolean` value.
`renderParents`    | Whether parents should be rendered if only rendering active branch. Expects a `boolean` value.


## Basic usage

This example shows how to render a menu from a container registered/found in the
view helper. Notice how pages are filtered out based on visibility and ACL.

In a view script or layout:

```php
<?= $this->navigation()->menu()->render() ?>
```

Or:

```php
<?= $this->navigation()->menu() ?>
```

Output:

```html
<ul class="navigation">
    <li>
        <a title="Go Home" href="/">Home</a>
    </li>
    <li class="active">
        <a href="/products">Products</a>
        <ul>
            <li class="active">
                <a href="/products/server">Foo Server</a>
                <ul>
                    <li class="active">
                        <a href="/products/server/faq">FAQ</a>
                    </li>
                    <li>
                        <a href="/products/server/editions">Editions</a>
                    </li>
                    <li>
                        <a href="/products/server/requirements">System Requirements</a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="/products/studio">Foo Studio</a>
                <ul>
                    <li>
                        <a href="/products/studio/customers">Customer Stories</a>
                    </li>
                    <li>
                        <a href="/products/studio/support">Support</a>
                    </li>
                </ul>
            </li>
        </ul>
    </li>
    <li>
        <a title="About us" href="/company/about">Company</a>
        <ul>
            <li>
                <a href="/company/about/investors">Investor Relations</a>
            </li>
            <li>
                <a class="rss" href="/company/news">News</a>
                <ul>
                    <li>
                        <a href="/company/news/press">Press Releases</a>
                    </li>
                    <li>
                        <a href="/archive">Archive</a>
                    </li>
                </ul>
            </li>
        </ul>
    </li>
    <li>
        <a href="/community">Community</a>
        <ul>
            <li>
                <a href="/community/account">My Account</a>
            </li>
            <li>
                <a class="external" href="http://forums.example.com/">Forums</a>
            </li>
        </ul>
    </li>
</ul>
```

## Calling renderMenu() directly

This example shows how to render a menu that is not registered in the view
helper by calling `renderMenu()` directly and specifying options.

```php
<?php
// render only the 'Community' menu
$community = $this->navigation()->findOneByLabel('Community');
$options = [
    'indent'  => 16,
    'ulClass' => 'community'
];
echo $this->navigation()
          ->menu()
          ->renderMenu($community, $options);
?>
```

Output:

```html
<ul class="community">
    <li>
        <a href="/community/account">My Account</a>
    </li>
    <li>
        <a class="external" href="http://forums.example.com/">Forums</a>
    </li>
</ul>
```

## Rendering the deepest active menu

This example shows how `renderSubMenu()` will render the deepest sub menu of
the active branch.

Calling `renderSubMenu($container, $ulClass, $indent)` is equivalent to calling
`renderMenu($container, $options)` with the following options:

```php
[
    'ulClass'          => $ulClass,
    'indent'           => $indent,
    'minDepth'         => null,
    'maxDepth'         => null,
    'onlyActiveBranch' => true,
    'renderParents'    => false,
]
```

Usage of `renderSubMenu` method:

```php
<?= $this->navigation()
    ->menu()
    ->renderSubMenu(null, 'sidebar', 4) ?>
```

The output will be the same if 'FAQ' or 'Foo Server' is active:

```html
<ul class="sidebar">
    <li class="active">
        <a href="/products/server/faq">FAQ</a>
    </li>
    <li>
        <a href="/products/server/editions">Editions</a>
    </li>
    <li>
        <a href="/products/server/requirements">System Requirements</a>
    </li>
</ul>
```

## Rendering with maximum depth

```php
<?= $this->navigation()
    ->menu()
    ->setMaxDepth(1) ?>
```

Output:

```html
<ul class="navigation">
    <li>
        <a title="Go Home" href="/">Home</a>
    </li>
    <li class="active">
        <a href="/products">Products</a>
        <ul>
            <li class="active">
                <a href="/products/server">Foo Server</a>
            </li>
            <li>
                <a href="/products/studio">Foo Studio</a>
            </li>
        </ul>
    </li>
    <li>
        <a title="About us" href="/company/about">Company</a>
        <ul>
            <li>
                <a href="/company/about/investors">Investor Relations</a>
            </li>
            <li>
                <a class="rss" href="/company/news">News</a>
            </li>
        </ul>
    </li>
    <li>
        <a href="/community">Community</a>
        <ul>
            <li>
                <a href="/community/account">My Account</a>
            </li>
            <li>
                <a class="external" href="http://forums.example.com/">Forums</a>
            </li>
        </ul>
    </li>
</ul>
```

## Rendering with minimum depth

```php
<?= $this->navigation()
    ->menu()
    ->setMinDepth(1) ?>
```

Output:

```html
<ul class="navigation">
    <li class="active">
        <a href="/products/server">Foo Server</a>
        <ul>
            <li class="active">
                <a href="/products/server/faq">FAQ</a>
            </li>
            <li>
                <a href="/products/server/editions">Editions</a>
            </li>
            <li>
                <a href="/products/server/requirements">System Requirements</a>
            </li>
        </ul>
    </li>
    <li>
        <a href="/products/studio">Foo Studio</a>
        <ul>
            <li>
                <a href="/products/studio/customers">Customer Stories</a>
            </li>
            <li>
                <a href="/products/studio/support">Support</a>
            </li>
        </ul>
    </li>
    <li>
        <a href="/company/about/investors">Investor Relations</a>
    </li>
    <li>
        <a class="rss" href="/company/news">News</a>
        <ul>
            <li>
                <a href="/company/news/press">Press Releases</a>
            </li>
            <li>
                <a href="/archive">Archive</a>
            </li>
        </ul>
    </li>
    <li>
        <a href="/community/account">My Account</a>
    </li>
    <li>
        <a class="external" href="http://forums.example.com/">Forums</a>
    </li>
</ul>
```

## Rendering only the active branch

```php
<?= $this->navigation()
    ->menu()
    ->setOnlyActiveBranch(true) ?>
```

Output:

```html
<ul class="navigation">
    <li class="active">
        <a href="/products">Products</a>
        <ul>
            <li class="active">
                <a href="/products/server">Foo Server</a>
                <ul>
                    <li class="active">
                        <a href="/products/server/faq">FAQ</a>
                    </li>
                    <li>
                        <a href="/products/server/editions">Editions</a>
                    </li>
                    <li>
                        <a href="/products/server/requirements">System Requirements</a>
                    </li>
                </ul>
            </li>
        </ul>
    </li>
</ul>
```

## Rendering only the active branch with minimum depth

```php
<?= $this->navigation()
    ->menu()
    ->setOnlyActiveBranch(true)
    ->setMinDepth(1) ?>
```

Output:

```html
<ul class="navigation">
    <li class="active">
        <a href="/products/server">Foo Server</a>
        <ul>
            <li class="active">
                <a href="/products/server/faq">FAQ</a>
            </li>
            <li>
                <a href="/products/server/editions">Editions</a>
            </li>
            <li>
                <a href="/products/server/requirements">System Requirements</a>
            </li>
        </ul>
    </li>
</ul>
```

## Rendering only the active branch with maximum depth

```php
<?= $this->navigation()
    ->menu()
    ->setOnlyActiveBranch(true)
    ->setMaxDepth(1) ?>
```

Output:

```html
<ul class="navigation">
    <li class="active">
        <a href="/products">Products</a>
        <ul>
            <li class="active">
                <a href="/products/server">Foo Server</a>
            </li>
            <li>
                <a href="/products/studio">Foo Studio</a>
            </li>
        </ul>
    </li>
</ul>
```

## Rendering only the active branch with maximum depth and no parents

```php
<?= $this->navigation()
    ->menu()
    ->setOnlyActiveBranch(true)
    ->setRenderParents(false)
    ->setMaxDepth(1) ?>
```

Output:

```html
<ul class="navigation">
    <li class="active">
        <a href="/products/server">Foo Server</a>
    </li>
    <li>
        <a href="/products/studio">Foo Studio</a>
    </li>
</ul>
```

## Rendering a custom menu using a partial view script

This example shows how to render a custom menu using a partial view script. By
calling `setPartial()`, you can specify a partial view script that will be used
when calling `render()`; when a partial is specified, that method will proxy to
the `renderPartial()` method.

The `renderPartial()`  method will assign the container to the view with the key
`container`.

In a layout:

```php
$this->navigation()->menu()->setPartial('my-module/partials/menu');
echo $this->navigation()->menu()->render();
```

In `module/MyModule/view/my-module/partials/menu.phtml`:

```php
foreach ($this->container as $page) {
    echo $this->navigation()->menu()->htmlify($page) . PHP_EOL;
}
```

Output:

```html
<a title="Go Home" href="/">Home</a>
<a href="/products">Products</a>
<a title="About us" href="/company/about">Company</a>
<a href="/community">Community</a>
```

### Using additional parameters in partial view scripts

Starting with version 2.6.0, you can assign custom variables to a
partial script.

In a layout:

```php
// Set partial
$this->navigation()->menu()->setPartial('my-module/partials/menu');

// Output menu
echo $this->navigation()->menu()->renderPartialWithParams(
    [
        'headline' => 'Links',
    ]
);
```

In `module/MyModule/view/my-module/partials/menu.phtml`:

```php
<h1><?= $headline ?></h1>

<?php
foreach ($this->container as $page) {
    echo $this->navigation()->menu()->htmlify($page) . PHP_EOL;
}
?>
```

Output:

```html
<h1>Links</h1>
<a title="Go Home" href="/">Home</a>
<a href="/products">Products</a>
<a title="About us" href="/company/about">Company</a>
<a href="/community">Community</a>
```

### Using menu options in partial view scripts

In a layout:

```php
// Set options
$this->navigation()->menu()
    ->setUlClass('my-nav')
    ->setPartial('my-module/partials/menu');

// Output menu
echo $this->navigation()->menu()->render();
```

In `module/MyModule/view/my-module/partials/menu.phtml`:

```php
<div class"<?= $this->navigation()->menu()->getUlClass() ?>">
    <?php
    foreach ($this->container as $page) {
        echo $this->navigation()->menu()->htmlify($page) . PHP_EOL;
    }
    ?>
</div>
```

Output:

```html
<div class="my-nav">
    <a title="Go Home" href="/">Home</a>
    <a href="/products">Products</a>
    <a title="About us" href="/company/about">Company</a>
    <a href="/community">Community</a>
</div>
```

### Using ACLs with partial view scripts

If you want to use an ACL within your partial view script, then you will have to
check the access to a page manually.

In `module/MyModule/view/my-module/partials/menu.phtml`:

```php
foreach ($this->container as $page) {
    if ($this->navigation()->accept($page)) {
        echo $this->navigation()->menu()->htmlify($page) . PHP_EOL;
    }
}
```

# View Helper - Sitemap

The `sitemap()` helper is used for generating XML sitemaps, as defined by the
[Sitemaps XML format](http://www.sitemaps.org/protocol.php). Read more about
[Sitemaps on Wikipedia](http://en.wikipedia.org/wiki/Sitemaps).

By default, the sitemap helper uses [sitemap validators](https://github.com/laminas/laminas-validator)
to validate each element that is rendered. This can be disabled by calling
`$helper->setUseSitemapValidators(false)`.

### Sitemap XML elements

Element    | Type   | Description
---------- | ------ | -----------
loc        | string | Absolute URL to page. An absolute URL will be generated by the helper.
lastmod    | string | The date of last modification of the file, in W3C Datetime format. This time portion can be omitted if desired, and only use YYYY-MM-DD. The helper will try to retrieve the lastmod value from the page's custom property lastmod if it is set in the page. If the value is not a valid date, it is ignored.
changefreq | string | How frequently the page is likely to change. This value provides general information to search engines and may not correlate exactly to how often they crawl the page. Valid values are: "always", "hourly", "daily", "weekly", "monthly", "yearly", and "never". The helper will try to retrieve the changefreq value from the page's custom property changefreq if it is set in the page. If the value is not valid, it is ignored.
priority   | float  | The priority of this URL relative to other URLs on your site. Valid values range from 0.0 to 1.0. The helper will try to retrieve the priority value from the page's custom property priority if it is set in the page. If the value is not valid, it is ignored.

> ### Validation only when enabled
>
> If you disable sitemap validators, the custom properties (see table) are not
> validated at all.

The sitemap helper also supports [Sitemap XSD Schema](https://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd)
validation of the generated sitemap. This is disabled by default, since it will
require a request to the schema file. It can be enabled with
`$helper->setUseSchemaValidation(true)`.

Methods in the sitemap helper:

Method signature                                        | Description
------------------------------------------------------- | -----------
`getFormatOutput() : bool`                              | Retrieve the flag indicating whether or not generated XML should be formatted. Default is `false`.
`setFormatOutput(bool $flag) : self`                    | Set the flag indicating whether or not generated XML should be formatted. The flag corresponds to the the `formatOutput` property of the native `DOMDocument` class. Read more in the [DOMDocument documentation](http://php.net/domdocument).
`getUseXmlDeclaration() : bool`                         | Retrieve the flag indicating whether or not to emit the XML declaration when rendering; defaults to `true`.
`setUseXmlDeclaration(bool $flag) : self`               | Set the flag indicating whether or not to emit the XML declaration when rendering.
`getUseSitemapValidators() : bool`                      | Retrieve the flag indicating whether or not sitemap validators should be used when generating the DOM; default is `true`.
`setUseSitemapValidators(bool $flag) : self`            | Set the flag indicating whether or not sitemap validators should be used when generating the DOM.
`getUseSchemaValidation() : bool`                       | Retrieve the flag indicating whether or not the helper should use XML schema validation when generating the DOM; default is `false`.
`setUseSchemaValidation(bool $flag) : self`             | Set the flag indicating whether or not the helper should use XML schema validation when generating the DOM.
`getServerUrl() : string`                               | Retrieve the server URL to prepend to non-absolute URIs via the `url()` method; if none is present, it will be determined by the helper.
`setServerUrl(string $url) : self`                      | Set the base server URL to prepend to non-absolute URIs.
`url(PageInterface $page) : string`                      | Generate an absolute URL for the provided page.
`getDomSitemap(ContainerInterface = null) : DOMDocument` | Generates a DOMDocument sitemap representation from the given container.

## Basic usage

This example shows how to render an *XML* sitemap based on the setup we did further up.

```php
// In a view script or layout:

// format output
$this->navigation()
      ->sitemap()
      ->setFormatOutput(true); // default is false

// Other possible methods:
// ->setUseXmlDeclaration(false);             // default is true
// ->setServerUrl('http://my.otherhost.com'); // default is to detect automatically

// print sitemap
echo $this->navigation()->sitemap();
```

Notice how pages that are invisible or pages with ACL roles incompatible with
the view helper are filtered out:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>http://www.example.com/</loc>
  </url>
  <url>
    <loc>http://www.example.com/products</loc>
  </url>
  <url>
    <loc>http://www.example.com/products/server</loc>
  </url>
  <url>
    <loc>http://www.example.com/products/server/faq</loc>
  </url>
  <url>
    <loc>http://www.example.com/products/server/editions</loc>
  </url>
  <url>
    <loc>http://www.example.com/products/server/requirements</loc>
  </url>
  <url>
    <loc>http://www.example.com/products/studio</loc>
  </url>
  <url>
    <loc>http://www.example.com/products/studio/customers</loc>
  </url>
  <url>
    <loc>http://www.example.com/products/studio/support</loc>
  </url>
  <url>
    <loc>http://www.example.com/company/about</loc>
  </url>
  <url>
    <loc>http://www.example.com/company/about/investors</loc>
  </url>
  <url>
    <loc>http://www.example.com/company/news</loc>
  </url>
  <url>
    <loc>http://www.example.com/company/news/press</loc>
  </url>
  <url>
    <loc>http://www.example.com/archive</loc>
  </url>
  <url>
    <loc>http://www.example.com/community</loc>
  </url>
  <url>
    <loc>http://www.example.com/community/account</loc>
  </url>
  <url>
    <loc>http://forums.example.com/</loc>
  </url>
</urlset>
```

## Rendering using no ACL role

Render the sitemap using no ACL role (should filter out `/community/account`):

```php
echo $this->navigation()->sitemap()
    ->setFormatOutput(true)
    ->setRole();
```

Output:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>http://www.example.com/</loc>
  </url>
  <url>
    <loc>http://www.example.com/products</loc>
  </url>
  <url>
    <loc>http://www.example.com/products/server</loc>
  </url>
  <url>
    <loc>http://www.example.com/products/server/faq</loc>
  </url>
  <url>
    <loc>http://www.example.com/products/server/editions</loc>
  </url>
  <url>
    <loc>http://www.example.com/products/server/requirements</loc>
  </url>
  <url>
    <loc>http://www.example.com/products/studio</loc>
  </url>
  <url>
    <loc>http://www.example.com/products/studio/customers</loc>
  </url>
  <url>
    <loc>http://www.example.com/products/studio/support</loc>
  </url>
  <url>
    <loc>http://www.example.com/company/about</loc>
  </url>
  <url>
    <loc>http://www.example.com/company/about/investors</loc>
  </url>
  <url>
    <loc>http://www.example.com/company/news</loc>
  </url>
  <url>
    <loc>http://www.example.com/company/news/press</loc>
  </url>
  <url>
    <loc>http://www.example.com/archive</loc>
  </url>
  <url>
    <loc>http://www.example.com/community</loc>
  </url>
  <url>
    <loc>http://forums.example.com/</loc>
  </url>
</urlset>
```

## Rendering using a maximum depth

Render the sitemap using a maximum depth of 1.

```php
echo $this->navigation()->sitemap()
    ->setFormatOutput(true)
    ->setMaxDepth(1);
```

Output:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>http://www.example.com/</loc>
  </url>
  <url>
    <loc>http://www.example.com/products</loc>
  </url>
  <url>
    <loc>http://www.example.com/products/server</loc>
  </url>
  <url>
    <loc>http://www.example.com/products/studio</loc>
  </url>
  <url>
    <loc>http://www.example.com/company/about</loc>
  </url>
  <url>
    <loc>http://www.example.com/company/about/investors</loc>
  </url>
  <url>
    <loc>http://www.example.com/company/news</loc>
  </url>
  <url>
    <loc>http://www.example.com/community</loc>
  </url>
  <url>
    <loc>http://www.example.com/community/account</loc>
  </url>
  <url>
    <loc>http://forums.example.com/</loc>
  </url>
</urlset>
```

> ### UTF-8 encoding used by default
>
> By default, laminas-view uses UTF-8 as its default encoding.  If you want to use
> another encoding with `Sitemap`, you will have do three things:
>
> 1. Create a custom renderer and implement a `getEncoding()` method.
> 2. Create a custom rendering strategy that will return an instance of your custom renderer.
> 3. Attach the custom strategy in the `ViewEvent`.
>
> See the [example from the HeadStyle documentation](https://github.com/laminas/laminas-view)
> to see how you can achieve this.


## License

This package is licensed using the MIT License.

Please have a look at [`LICENSE.md`](LICENSE.md).
