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
- It is possible to disable the internal page cache with this : ` \Drupal::service('page_cache_kill_switch')->trigger()` but if your block appears on every page, it is probably a bad idea.  
Also this works inside a controller build but doesn't seem to work inside a block build.