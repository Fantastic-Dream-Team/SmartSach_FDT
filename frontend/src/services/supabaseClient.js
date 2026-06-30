// supabaseClient.js
// Configuración del cliente de Supabase para el Frontend
import { createClient } from 'https://cdn.jsdelivr.net/npm/@supabase/supabase-js/+esm'

// Las variables de entorno de frontend pueden ser inyectadas por el bundler (Vite, Webpack, etc.)
// O bien ser pasadas a un bloque de configuración en el index.php si no hay bundler.
// Asumiendo que usamos variables procesadas o globales configuradas para el entorno:

const supabaseUrl = process.env.SUPABASE_URL || window.SUPABASE_URL;
const supabaseAnonKey = process.env.SUPABASE_ANON_KEY || window.SUPABASE_ANON_KEY;

if (!supabaseUrl || !supabaseAnonKey) {
    console.error('Falta la configuración de Supabase: SUPABASE_URL o SUPABASE_ANON_KEY no están definidos.');
}

export const supabase = createClient(supabaseUrl, supabaseAnonKey);
