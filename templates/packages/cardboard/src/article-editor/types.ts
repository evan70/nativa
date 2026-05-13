import type { Article as GeneratedArticle } from '../../../types/generated/Article';
import type { Media as GeneratedMedia } from '../../../types/generated/Media';

export type EditorMode = 'create' | 'edit';

export interface EditorCategoryOption {
  id: string;
  name: string;
}

export interface EditorTagOption {
  id: string;
  name: string;
}

export interface EditorMediaItem {
  id?: string;
  mediaId: string;
  url: string;
  mimeType?: string;
  filename?: string;
  size?: number;
  caption?: string;
  isImage: boolean;
}

export interface EditorArticleModel {
  id?: string;
  title: string;
  slug: string;
  excerpt: string;
  content: string;
  status: string;
  categoryId: string;
  tags: string[];
  image: string;
  media: EditorMediaItem[];
  metaTitle: string;
  metaDescription: string;
  metaKeywords: string;
  canonicalUrl: string;
  ogTitle: string;
  ogDescription: string;
  ogImage: string;
  twitterTitle: string;
  twitterDescription: string;
  twitterImage: string;
  schemaJson: string;
  scheduledAt: string;
  authorName: string;
}

export interface ArticleEditorConfig {
  mode: EditorMode;
  articleId?: string;
  csrfToken: string;
  submitUrl: string;
  mediaUploadUrl: string;
  mediaLibraryUrl: string;
  articleData: EditorArticleModel;
  categories?: EditorCategoryOption[];
  availableTags?: EditorTagOption[];
  onError?: (error: string) => void;
  onSubmit?: (data: FormData) => void;
}

export interface EditorState {
  isUploading: boolean;
  isSubmitting: boolean;
}

export interface UploadedMedia {
  id: string;
  url: string;
  filename: string;
  mimeType: string;
}

type ArticleSeoFields = {
  metaTitle?: string;
  metaDescription?: string;
  metaKeywords?: string;
  canonicalUrl?: string;
  ogTitle?: string;
  ogDescription?: string;
  ogImage?: string;
  twitterTitle?: string;
  twitterDescription?: string;
  twitterImage?: string;
  schemaJson?: string;
  scheduledAt?: string;
  authorName?: string;
};

type LegacyArticleFields = {
  category_id?: string | null;
  author_name?: string | null;
  meta_title?: string | null;
  meta_description?: string | null;
  meta_keywords?: string | null;
  canonical_url?: string | null;
  og_title?: string | null;
  og_description?: string | null;
  og_image?: string | null;
  twitter_title?: string | null;
  twitter_description?: string | null;
  twitter_image?: string | null;
  schema_json?: string | null;
  scheduled_at?: string | null;
  media?: Array<Partial<GeneratedMedia> & Record<string, unknown>>;
  slug?: string | null;
  image?: string | null;
  excerpt?: string | null;
};

export type RawArticleData = Partial<GeneratedArticle> & ArticleSeoFields & LegacyArticleFields & Record<string, unknown>;
export type RawCategoryData = Record<string, unknown>;
export type RawTagData = Record<string, unknown>;
