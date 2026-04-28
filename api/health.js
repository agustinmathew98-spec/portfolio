function json(res, statusCode, payload) {
  res.status(statusCode).setHeader("Content-Type", "application/json");
  res.end(JSON.stringify(payload));
}

export default function handler(req, res) {
  if (req.method !== "GET") {
    return json(res, 405, { success: false, message: "Method not allowed." });
  }

  const checks = {
    SUPABASE_URL: Boolean(process.env.SUPABASE_URL),
    SUPABASE_SERVICE_ROLE_KEY: Boolean(process.env.SUPABASE_SERVICE_ROLE_KEY),
    ADMIN_TOKEN: Boolean(process.env.ADMIN_TOKEN)
  };

  const missing = Object.entries(checks)
    .filter(([, exists]) => !exists)
    .map(([name]) => name);

  if (missing.length > 0) {
    return json(res, 500, {
      success: false,
      status: "misconfigured",
      missing
    });
  }

  return json(res, 200, {
    success: true,
    status: "ok",
    timestamp: new Date().toISOString()
  });
}
