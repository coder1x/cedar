import js from '@eslint/js'
import globals from 'globals'
import reactHooks from 'eslint-plugin-react-hooks'
import reactRefresh from 'eslint-plugin-react-refresh'
import testingLibrary from 'eslint-plugin-testing-library';
import prettier from 'eslint-plugin-prettier';
import tseslint from 'typescript-eslint'
import simpleImportSort from 'eslint-plugin-simple-import-sort';
import importPlugin from 'eslint-plugin-import';
import { defineConfig, globalIgnores } from 'eslint/config'

export default defineConfig([
  globalIgnores(['dist']),
  {
    files: ['**/src/**/*.{ts,tsx,js,jsx}'],
    extends: [
      js.configs.recommended,
      tseslint.configs.recommended,
        ...tseslint.configs.recommended,
      {
        ignores: [
          '**/.yarn/**',
          '.husky/**',
          '.nx/**',
          '.pnp.cjs',
          '.pnp.loader.mjs',
          '.vscode/**',
          '**/node_modules/**',
          '**/build/**/*.*',
          '**/*/setupTests.ts',
          '**/*/react-app-env.d.ts',
          '**/*/mock/**/*.*',
          '**/test-results/**/*.*',
          '**/mockServiceWorker.js',
        ],
      },
      reactHooks.configs.flat.recommended,
      reactRefresh.configs.vite,
    ],
    languageOptions: {
      ecmaVersion: 2020,
      globals: globals.browser,
    },
    plugins: {
      prettier,
      'testing-library': testingLibrary,
      'simple-import-sort': simpleImportSort,
      import: importPlugin,
      'react-hooks': reactHooks,
    },
    rules: {
      'react-hooks/rules-of-hooks': 'error',
      'react-hooks/exhaustive-deps': 'warn',
      'no-case-declarations': 'off',
      '@typescript-eslint/no-explicit-any': 'error',
      '@typescript-eslint/no-empty-interface': 'error',
      '@typescript-eslint/no-empty-object-type': 'warn',
      'simple-import-sort/exports': 'warn',
      'import/first': 'warn',
      'import/newline-after-import': 'warn',
      'import/no-duplicates': 'warn',
    }
  },
])
