/* Catalog — publications (never borrowed). Authors are many-to-many. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { libraryApi, type Book } from './api';

export function CatalogPage() {
  const { user } = useAuth();
  const [publishers, setPublishers] = useState<FieldOption[]>([]);
  const [categories, setCategories] = useState<FieldOption[]>([]);
  const [authors, setAuthors] = useState<FieldOption[]>([]);

  useEffect(() => {
    const f = { filter: { school_id: user?.school_id }, per_page: 200 };
    libraryApi.publishers
      .list(f)
      .then((r) => setPublishers(r.data.map((p) => ({ value: String(p.id), label: p.name }))));
    libraryApi.categories
      .list(f)
      .then((r) => setCategories(r.data.map((c) => ({ value: String(c.id), label: c.name }))));
    libraryApi.authors
      .list(f)
      .then((r) => setAuthors(r.data.map((a) => ({ value: String(a.id), label: a.name }))));
  }, [user?.school_id]);

  const fields: Field[] = [
    { name: 'title', label: 'Title', type: 'text', required: true },
    { name: 'subtitle', label: 'Subtitle', type: 'text' },
    { name: 'isbn', label: 'ISBN', type: 'text' },
    { name: 'edition', label: 'Edition', type: 'text' },
    { name: 'language', label: 'Language', type: 'text' },
    { name: 'publication_year', label: 'Publication year', type: 'number' },
    {
      name: 'publisher_id',
      label: 'Publisher',
      type: 'select',
      options: [{ value: '', label: '—' }, ...publishers],
    },
    {
      name: 'category_id',
      label: 'Category',
      type: 'select',
      options: [{ value: '', label: '—' }, ...categories],
    },
    { name: 'author_ids', label: 'Authors', type: 'multiselect', options: authors },
    { name: 'description', label: 'Description', type: 'text' },
  ];

  const columns: AXColumn<Book>[] = [
    {
      key: 'title',
      header: 'Title',
      render: (r) => <span className="font-medium">{r.title}</span>,
    },
    {
      key: 'isbn',
      header: 'ISBN',
      render: (r) => <code className="text-xs text-gray-500">{r.isbn ?? '—'}</code>,
    },
    {
      key: 'authors',
      header: 'Authors',
      render: (r) => r.authors?.map((a) => a.name).join(', ') || '—',
    },
    { key: 'category', header: 'Category', render: (r) => r.category ?? '—' },
    {
      key: 'copies',
      header: 'Copies',
      render: (r) => <AXBadge tone="navy">{r.copies_count ?? 0}</AXBadge>,
    },
  ];

  return (
    <EntityManager<Book>
      title="Catalog"
      icon="book"
      unitLabel="titles"
      api={libraryApi.catalog}
      columns={columns}
      fields={fields}
      emptyForm={{
        title: '',
        subtitle: '',
        isbn: '',
        edition: '',
        language: '',
        publication_year: '',
        publisher_id: '',
        category_id: '',
        author_ids: [],
        description: '',
      }}
      toForm={(r) => ({
        title: r.title,
        subtitle: r.subtitle ?? '',
        isbn: r.isbn ?? '',
        edition: r.edition ?? '',
        language: r.language ?? '',
        publication_year: r.publication_year ?? '',
        publisher_id: r.publisher_id ? String(r.publisher_id) : '',
        category_id: r.category_id ? String(r.category_id) : '',
        author_ids: (r.authors ?? []).map((a) => String(a.id)),
        description: r.description ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="title"
      searchPlaceholder="Search catalog…"
      sort="title"
    />
  );
}
