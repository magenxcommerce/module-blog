# Magenx_Blog

Minimal headless blog content module. Magento admin is the only authoring
surface (posts, categories, tags, related posts, related products); the
storefront is Next.js and consumes content exclusively over GraphQL. No
`view/frontend`, no theme templates, no InstallSchema-style migrations —
schema is declared in `etc/db_schema.xml` only.

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
Meta/SEO, Store Views. The Related Products tab is a searchable product
grid (checkbox selection + editable position), the same pattern core
Magento uses for assigning products to a category — not a free-text SKU
field — persisted to `magenx_blog_post_product` on save.

## Configuration

Stores > Configuration > **Magenx > Blog > General** (ACL:
`Magenx_Blog::config`):

| Field | Default | Effect |
| --- | --- | --- |
| Enable Blog | Yes | When No, `blogPosts` / `blogPost` / `blogTag` / `blogCategories` resolve to empty results for that store; no data is removed |
| Posts Per Page | 10 | Page size `blogPosts` uses when the query omits `pageSize` |
| Maximum Page Size | 50 | Upper bound a `blogPosts` query may request; larger values are clamped |

All three are store-scoped and read through `Magenx\Blog\Model\Config`.

## GraphQL

`Query.blogPosts(filter, pageSize, currentPage)`, `Query.blogPost(urlKey)`,
`Query.blogTag(urlKey)`, `Query.blogCategories`. `BlogPostFilterInput`
supports `url_key`, `category_id`, `tag_id` and `sku` — all resolved
server-side (no client-side full-list scanning required from the
storefront). See `etc/schema.graphqls` for the full, `@doc`'d surface.

## Install

```bash
bin/magento module:enable Magenx_Blog
bin/magento setup:upgrade
```

## Caveats

- No live Magento install was available to exercise this module during
  development; admin CRUD and the GraphQL surface still need a live
  `setup:upgrade` + manual smoke test (create a post, attach products,
  query `blogPost` from the storefront).
- Categories and tags are flat lists (no hierarchy) — matches how the
  storefront actually consumes them.
