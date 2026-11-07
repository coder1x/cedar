import {defineConfig} from 'vite';
import react, {reactCompilerPreset} from '@vitejs/plugin-react';
import babel from '@rolldown/plugin-babel';
import type {LoggerEvent} from 'babel-plugin-react-compiler';

export default defineConfig(({mode}) => {
  const isProd = mode === 'production';
  const base = isProd ? './' : '/';

  return {
    base,
    server: {
      host: 'localhost',
      port: 3000,
      open: true,
    },
    plugins: [
      react(),
      babel({
        presets: [
          reactCompilerPreset({
            logger: {
              logEvent(filename: string | null, event: LoggerEvent) {
                if (event.kind === 'CompileSuccess') {
                  console.log('✅ React Compiler: Compiled successfully -', filename);
                } else if (event.kind === 'CompileError') {
                  console.error('❌ React Compiler: Error in', filename, event);
                } else if (event.kind === 'CompileSkip') {
                  console.log('⏭️  React Compiler: Skipped -', filename);
                }
              },
            },
          }),
        ],
      }),
    ],
  };
});
