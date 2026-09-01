# Magenx_Blog

Minimal headless blog content module. Magento admin is the only authoring
surface (posts, categories, tags, related posts, related products); the
storefront is Next.js and consumes content exclusively over GraphQL, served by
the companion module `Magenx_BlogGraphQl`. No `view/frontend`, no theme
templates, no InstallSchema-style migrations — schema is declared in
`etc/db_schema.xml` only.

This module owns its own entities end to end. There is no stock Magento
"Blog" module it wraps or extends.

## Entities

| Table | Purpose |
| --- | --- |
| `magenx_blog_post` | Post content: title, short_description, content, image, url_key, publish_date, is_active, author_name, meta fields |
| `magenx_blog_post_store` | Store-view scoping for a post; `store_id = 0` means all store views (same convention as `cms_page_store`) |
| `magenx_blog_category` | Flat (non-hierarchical) category list |
| `magenx_blog_post_category` | Post ↔ category assignment |
| `magenx_blog_tag` | Tag list |
| `magenx_blog_post_tag` | Post ↔ tag assignment |
| `magenx_blog_post_product` | Post ↔ product relation (**related products**), with a `position` column; `product_id` cascades on product delete |
| `magenx_blog_post_related` | Post ↔ post relation (related posts), with a `position` column |

## Admin

Content > Blog: Posts, Categories, Tags. Post edit is a tabbed form —
General, Content, Categories & Tags, Related Products, Related Posts,
Meta/SEO, Store Views. Post Content uses the standard admin WYSIWYG
(TinyMCE + media browser, hence the `Magento_Cms` dependency); it degrades to
a textarea when WYSIWYG is disabled in Content > Design > Configuration.
The Related Products tab is a searchable product
grid (checkbox selection + editable position), the same pattern core
Magento uses for assigning products to a category — not a free-text SKU
field — persisted to `magenx_blog_post_product` on save.

## Configuration

Stores > Configuration > **Magenx > Blog > General** (ACL:
`Magenx_Blog::config`):

| Field | Default | Effect |
| --- | --- | --- |
| Enable Blog | Yes | When No, the `Magenx_BlogGraphQl` queries resolve to empty results for that store; no data is removed |
| Posts Per Page | 10 | Page size `blogPosts` uses when the query omits `pageSize` |
| Maximum Page Size | 50 | Upper bound a `blogPosts` query may request; larger values are clamped |

All three are store-scoped and read through `Magenx\Blog\Model\Config`.

## GraphQL

Not here. The storefront surface — `Query.blogPosts`, `blogPost`, `blogTag`,
`blogCategories` and their resolvers — lives in **`Magenx_BlogGraphQl`**
([magenxcommerce/module-blog-graph-ql](https://github.com/magenxcommerce/module-blog-graph-ql)),
the same split as `Magenx_Rma` / `Magenx_RmaGraphQl`. This module keeps the
entities, the admin and the configuration those resolvers read, so a
backend-only install carries no `Magento_GraphQl` dependency.

Installing `Magenx_Blog` alone gives you admin authoring and nothing on the
storefront. The filters the storefront needs (`url_key`, `category_id`,
`tag_id`, `sku`, store scope, published-only) are still implemented here, on
`Magenx\Blog\Model\ResourceModel\Post\Collection`.

## Install

```bash
bin/magento module:enable Magenx_Blog
bin/magento setup:upgrade
```

For the storefront, install `magenxcommerce/module-blog-graph-ql` as well.

## Caveats

- No live Magento install was available to exercise this module during
  development; admin CRUD still needs a live `setup:upgrade` + manual smoke
  test (create a post, attach products, query `blogPost` from the storefront
  with `Magenx_BlogGraphQl` installed).
- Saving a post, category or tag cleans its `magenx_blog_*` cache tags, so
  Magento's own cache invalidation PURGEs the storefront and the blog pages
  revalidate immediately rather than after the storefront's 30-minute blog
  TTL. The storefront maps those identities to its coarse `blog` tag in
  `apps/theme/src/app/api/revalidate/route.ts`.
- Post HTML is stored exactly as authored — there is no server-side
  sanitization. The Next.js storefront sanitizes `post_content` and
  `short_description` on read (`sanitizeCmsHtml`, `packages/engine`); a
  different consumer of the `Magenx_BlogGraphQl` surface has to do its own.
- Categories and tags are flat lists (no hierarchy) — matches how the
  storefront actually consumes them.
- The storefront filters on `Magenx\Blog\Model\ResourceModel\Post\Collection`
  (store scope, category, tag, sku) are semi-joins against
  `main_table.post_id`, not JOINs. Every relation table carries its own
  `post_id`, and a joined-in table both made later `post_id` filters
  ambiguous and inflated `getSize()` — a post assigned to *both* "All Store
  Views" and a specific store matched `post_store` twice, and Magento counts
  joined rows. Adding a new relation filter should follow the same shape.
