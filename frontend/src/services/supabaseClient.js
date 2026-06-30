// supabaseClient.js
// Configuración del cliente de Supabase para el Frontend
import { createClient } from 'https://cdn.jsdelivr.net/npm/@supabase/supabase-js/+esm'

// Obtenemos las variables inyectadas globalmente en la ventana por PHP
const supabaseUrl = window.SUPABASE_URL;
const supabaseAnonKey = window.SUPABASE_ANON_KEY;

if (!supabaseUrl || !supabaseAnonKey) {
    console.error('Falta la configuración de Supabase: SUPABASE_URL o SUPABASE_ANON_KEY no están definidos en window.');
}

export const supabase = createClient(supabaseUrl, supabaseAnonKey);

