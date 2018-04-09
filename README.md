# BigPipeTest
Small project to test BigPipe

## CACHE
### INTERNAL PAGE CACHE
- When using internal page cache, the max-age has no effect :
    - https://drupal.stackexchange.com/questions/196469/anonymous-user-cache-control
    > The Internal Page Cache is for complete pages for anonymous users and only uses cache tags.
    - https://webdev.iac.gatech.edu/blog/drupal-8-caching-for-dummies
    > Drupal 8 version does not use expiration timestamps
- It is possible to use cache tags to invalidate an internal page cache
    - See also previous links from the previous point.
    - https://www.drupal.org/docs/8/administering-drupal-8-site/internal-page-cache
    > Drupal 7 required the entire page cache to be cleared whenever any content is modified; Drupal 8 uses cache tags to only clear the cached pages that depend on the modified content.
    - https://drupal.stackexchange.com/questions/245098/set-maximum-disable-custom-block-cache-for-anonymous-users
- This settings `admin/config/development/performance` doesn't seem to work as intended :
    - Even when I set this to 1 min the cache is not rebuilt
    - See also: https://drupal.stackexchange.com/questions/196469/anonymous-user-cache-control
- It is possible to disable the internal page cache with this : `\Drupal::service('page_cache_kill_switch')->trigger()` but if your block appears on every page, it is probably a bad idea.  
Also this works inside a controller build but doesn't seem to work inside a block build.

## ORDERING FLUSHES
By default BigPipe returns placeholders in DOM order and most of the time it should be ok. But if you have an element that appears early in the DOM that for example send a request to an external service, you might want to change this behaviour (weather widget or online/offline status) to avoid delaying the display of the remaining of the page for that.  

- I was hoping for some asyncronous way ("When its ready"), but it doesn't seem possible.
```
BigPipe->sendContent
    BigPipe->sendPreBody    // all before final body tag </body>
    BigPipe->sendPlaceholders
        foreach // placeholder in order
            BigPipe->renderPlaceholder
                Renderer->renderPlaceholder
                    Renderer->renderPlain
                        Renderer->render
                            Renderer->doRender  // a lot of process around the lazy_builder and create_placeholder
                                $new_elements = call_user_func_array($callable, $args); // $callable being the lazy builder callback
            $ajax_response->addCommand  // with the final element to replace the placeholder
            BigPipe->sendChunk
    BigPipe->sendPlaceholders
```
- We can change this behaviour by overriding the `getPlaceholderOrder` method from the `big-pipe` service. This is possible by using the lazy builder callback name to identify elements you want to sort. This is how it is done for drupal Status messages in the Core. The problem with this method is that it is hardcoded! Note also that 2 modules doing so won't be compatible.
- Lazy builders only allow a few keys in the render array. So we cannot create our own key.
- The '#weight' is an allowed key with lazy_builder but not used by it. We could override the `render_placeholder_generator` service to let this key inside lazy_builder arrays. BUT it is used to sort children (blocks for example), so changing the weight for blocks will change its position. In the end this is not really a good idea.
- We could probably use a centralize service as the lazy callback that takes a weight in parameter. By combining this with the first method, it should work.
