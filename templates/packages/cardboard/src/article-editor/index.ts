export { ArticleEditor } from './controller';
export { default } from './controller';
export {
  normalizeArticleData,
  normalizeCategories,
  normalizeMediaItem,
  normalizeTags,
} from './normalizers';
export {
  queueSuccessAndRedirect,
  submitArticle,
  validateSubmission,
} from './submit';
export type {
  ArticleEditorConfig,
  EditorArticleModel,
  EditorCategoryOption,
  EditorMediaItem,
  EditorTagOption,
  RawArticleData,
} from './types';