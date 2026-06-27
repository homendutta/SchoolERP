/*
 * Footer — minimal app footer shown beneath the main content area.
 */
export function Footer() {
  const year = new Date().getFullYear();
  return (
    <footer className="border-t border-black/5 bg-white px-4 py-3 text-center text-xs text-gray-400">
      © {year} Asylinx School ERP · v0.1.0
    </footer>
  );
}
