import type {
  EditorArticleModel,
  EditorCategoryOption,
  EditorMediaItem,
  EditorTagOption,
  RawArticleData,
  RawCategoryData,
  RawTagData,
} from './types';

function asString(value: unknown): string {
  return typeof value === 'string' ? value : '';
}

function asStringArray(value: unknown): string[] {
  if (!Array.isArray(value)) {
    return [];
  }

  return value.filter((item): item is string => typeof item === 'string');
}

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null;
}

export function normalizeMediaItem(raw: unknown): EditorMediaItem | null {
  if (!isRecord(raw)) {
    return null;
  }

  const mediaId = asString(raw.mediaId ?? raw.media_id ?? raw.id);
  const url = asString(raw.url ?? raw.path);
  const mimeType = asString(raw.mimeType ?? raw.mime_type);
  const filename = asString(raw.filename ?? raw.originalName ?? raw.original_name);
  const caption = asString(raw.caption);
  const size = typeof raw.size === 'number' ? raw.size : undefined;
  const isImage = typeof raw.isImage === 'boolean'
    ? raw.isImage
    : typeof raw.is_image === 'boolean'
      ? raw.is_image
      : mimeType.startsWith('image/');

  return {
    id: asString(raw.id) || undefined,
    mediaId,
    url,
    mimeType: mimeType || undefined,
    filename: filename || undefined,
    size,
    caption: caption || undefined,
    isImage,
  };
}

export function normalizeArticleData(raw: RawArticleData | Record<string, unknown>): EditorArticleModel {
  const media = Array.isArray(raw.media) ? raw.media.map(normalizeMediaItem).filter((item): item is EditorMediaItem => item !== null) : [];

  return {
    id: asString(raw.id) || undefined,
    title: asString(raw.title),
    slug: asString(raw.slug),
    excerpt: asString(raw.excerpt),
    content: asString(raw.content),
    status: asString(raw.status) || 'draft',
    categoryId: asString(raw.categoryId ?? raw.category_id),
    tags: asStringArray(raw.tags),
    image: asString(raw.image),
    media,
    metaTitle: asString(raw.metaTitle ?? raw.meta_title),
    metaDescription: asString(raw.metaDescription ?? raw.meta_description),
    metaKeywords: asString(raw.metaKeywords ?? raw.meta_keywords),
    canonicalUrl: asString(raw.canonicalUrl ?? raw.canonical_url),
    ogTitle: asString(raw.ogTitle ?? raw.og_title),
    ogDescription: asString(raw.ogDescription ?? raw.og_description),
    ogImage: asString(raw.ogImage ?? raw.og_image),
    twitterTitle: asString(raw.twitterTitle ?? raw.twitter_title),
    twitterDescription: asString(raw.twitterDescription ?? raw.twitter_description),
    twitterImage: asString(raw.twitterImage ?? raw.twitter_image),
    schemaJson: asString(raw.schemaJson ?? raw.schema_json),
    scheduledAt: asString(raw.scheduledAt ?? raw.scheduled_at),
    authorName: asString(raw.authorName ?? raw.author_name),
  };
}

export function normalizeCategories(raw: unknown): EditorCategoryOption[] {
  if (!Array.isArray(raw)) {
    return [];
  }

  return raw
    .filter(isRecord)
    .map((item: RawCategoryData) => ({
      id: asString(item.id),
      name: asString(item.name),
    }))
    .filter((item) => '' !== item.id && '' !== item.name);
}

export function normalizeTags(raw: unknown): EditorTagOption[] {
  if (!Array.isArray(raw)) {
    return [];
  }

  return raw
    .filter(isRecord)
    .map((item: RawTagData) => ({
      id: asString(item.id),
      name: asString(item.name),
    }))
    .filter((item) => '' !== item.id && '' !== item.name);
}
