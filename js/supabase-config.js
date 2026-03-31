// Configuration Supabase pour Les Ailes de Sénart
window.SUPABASE_URL = "https://hksbiafznhdjngzimypi.supabase.co";
window.SUPABASE_KEY = "sb_publishable_odIBMR0BBSPQiJrcyma9NQ_sumPmxew";

// 2. Initialisation du client Supabase (Session éphémère : disparaît à la fermeture du navigateur)
if (window.supabase && typeof window.supabase.createClient === 'function') {
  window.supabaseClient = window.supabase.createClient(window.SUPABASE_URL, window.SUPABASE_KEY, {
    auth: {
      persistSession: true,
      storage: window.sessionStorage, // Supprime la session quand on ferme l'onglet/fenêtre
      autoRefreshToken: true
    }
  });
} else {
  console.error("Supabase library not loaded. Check CDN link.");
}

/**
 * Fonction helper pour vérifier si l'utilisateur actuel est "approuvé"
 */
async function checkMemberStatus() {
  const { data: { user } } = await window.supabaseClient.auth.getUser();
  if (!user) return null;

  const { data: profile, error } = await window.supabaseClient
    .from('profiles')
    .select('is_approved, is_admin')
    .eq('id', user.id)
    .single();

  if (error) return null;
  return profile;
}
