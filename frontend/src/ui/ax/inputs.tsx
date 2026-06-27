/* AX reusable inputs: AXInput, AXSelect, AXSearch. */
import { useEffect, useState, type InputHTMLAttributes, type SelectHTMLAttributes } from 'react';

interface AXInputProps extends InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
}

export function AXInput({ label, error, className = '', ...props }: AXInputProps) {
  return (
    <label className="block">
      {label && <span className="mb-1 block text-sm font-medium text-gray-700">{label}</span>}
      <input
        className={`w-full rounded-md border px-3 py-2 text-sm outline-none focus:border-[var(--navy-accent)] ${
          error ? 'border-[var(--danger)]' : 'border-gray-300'
        } ${className}`}
        {...props}
      />
      {error && <span className="mt-1 block text-xs text-[var(--danger)]">{error}</span>}
    </label>
  );
}

interface AXSelectProps extends SelectHTMLAttributes<HTMLSelectElement> {
  label?: string;
  options: { value: string; label: string }[];
}

export function AXSelect({ label, options, className = '', ...props }: AXSelectProps) {
  return (
    <label className="block">
      {label && <span className="mb-1 block text-sm font-medium text-gray-700">{label}</span>}
      <select
        className={`w-full rounded-md border border-gray-300 px-3 py-2 text-sm outline-none focus:border-[var(--navy-accent)] ${className}`}
        {...props}
      >
        {options.map((o) => (
          <option key={o.value} value={o.value}>
            {o.label}
          </option>
        ))}
      </select>
    </label>
  );
}

interface AXSearchProps {
  value?: string;
  placeholder?: string;
  onSearch: (term: string) => void;
  delay?: number;
}

/** Debounced search box. */
export function AXSearch({ value = '', placeholder = 'Search…', onSearch, delay = 350 }: AXSearchProps) {
  const [term, setTerm] = useState(value);
  useEffect(() => {
    const id = setTimeout(() => onSearch(term), delay);
    return () => clearTimeout(id);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [term]);

  return (
    <div className="flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-500">
      <i className="fas fa-search" />
      <input
        value={term}
        onChange={(e) => setTerm(e.target.value)}
        placeholder={placeholder}
        className="w-full bg-transparent outline-none"
      />
    </div>
  );
}
