const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function json(res, statusCode, payload) {
  res.status(statusCode).setHeader("Content-Type", "application/json");
  res.end(JSON.stringify(payload));
}

function sanitize(value) {
  return String(value || "").trim();
}

export default async function handler(req, res) {
  if (req.method !== "POST") {
    return json(res, 405, { success: false, message: "Method not allowed." });
  }

  try {
    const { name, email, subject, message } = req.body || {};

    const cleanName = sanitize(name);
    const cleanEmail = sanitize(email).toLowerCase();
    const cleanSubject = sanitize(subject);
    const cleanMessage = sanitize(message);

    if (!cleanName || !cleanEmail || !cleanSubject || !cleanMessage) {
      return json(res, 400, { success: false, message: "All fields are required." });
    }

    if (!EMAIL_REGEX.test(cleanEmail)) {
      return json(res, 400, { success: false, message: "Please enter a valid email address." });
    }

    if (cleanMessage.length < 10) {
      return json(res, 400, { success: false, message: "Message should be at least 10 characters." });
    }

    const supabaseUrl = process.env.SUPABASE_URL;
    const supabaseServiceRoleKey = process.env.SUPABASE_SERVICE_ROLE_KEY;

    if (!supabaseUrl || !supabaseServiceRoleKey) {
      return json(res, 500, {
        success: false,
        message: "Server is missing Supabase environment variables."
      });
    }

    const endpoint = `${supabaseUrl.replace(/\/$/, "")}/rest/v1/contact_messages`;
    const payload = {
      name: cleanName,
      email: cleanEmail,
      subject: cleanSubject,
      message: cleanMessage
    };

    const response = await fetch(endpoint, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        apikey: supabaseServiceRoleKey,
        Authorization: `Bearer ${supabaseServiceRoleKey}`,
        Prefer: "return=representation"
      },
      body: JSON.stringify(payload)
    });

    if (!response.ok) {
      const errorText = await response.text();
      console.error("Supabase insert failed:", errorText);
      return json(res, 500, { success: false, message: "Could not save your message right now." });
    }

    return json(res, 200, { success: true, message: "Thanks! Your message has been sent." });
  } catch (error) {
    console.error("Contact API error:", error);
    return json(res, 500, { success: false, message: "Unexpected server error." });
  }
}
