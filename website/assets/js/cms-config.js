/*
 * CMS integration config for the public website.
 *
 * Point `baseUrl` at the ERP API host (same origin in production, or the full
 * origin during local development). `schoolId` selects which school's published
 * content this site renders. Nothing else on the site needs to change — every
 * dynamic section reads from the read-only /cms/public/* API.
 */
window.CMS_CONFIG = {
  baseUrl: '/api/v1',
  schoolId: 1,
};
