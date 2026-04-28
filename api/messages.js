function json(res, statusCode, payload) {
  res.status(statusCode).setHeader("Content-Type", "application/json");
  res.end(JSON.stringify(payload));
}

export default async function handler(req, res) {
  if (req.method !== "GET") {
    return json(res, 405, { success: false, message: "Method not allowed." });
  }

  try {
    const providedToken = req.headers["x-admin-token"];
    const adminToken = process.env.ADMIN_TOKEN;

    if (!adminToken || providedToken !== adminToken) {
      return json(res, 401, { success: false, message: "Unauthorized." });
    }

    const supabaseUrl = process.env.SUPABASE_URL;
    const supabaseServiceRoleKey = process.env.SUPABASE_SERVICE_ROLE_KEY;

    if (!supabaseUrl || !supabaseServiceRoleKey) {
      return json(res, 500, {
        success: false,
        message: "Server is missing Supabase environment variables."
      });
    }

    const endpoint =
      `${supabaseUrl.replace(/\/$/, "")}` +
      "/rest/v1/contact_messages?select=id,name,email,subject,message,created_at&order=created_at.desc";

    const response = await fetch(endpoint, {
      method: "GET",
      headers: {
        apikey: supabaseServiceRoleKey,
        Authorization: `Bearer ${supabaseServiceRoleKey}`
      }
    });

    if (!response.ok) {
      const errorText = await response.text();
      console.error("Supabase read failed:", errorText);
      return json(res, 500, { success: false, message: "Could not load messages right now." });
    }

    const messages = await response.json();
    return json(res, 200, { success: true, messages });
  } catch (error) {
    console.error("Messages API error:", error);
    return json(res, 500, { success: false, message: "Unexpected server error." });
  }
}
