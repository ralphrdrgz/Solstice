import { db } from "../../db/index.js";
import { formSubmissions } from "../../db/schema.js";

export default async (req) => {
  if (req.method !== 'POST') {
    return new Response(null, {
      status: 302,
      headers: { Location: '/index.html' },
    })
  }

  const formData = await req.formData()
  const full_name = (formData.get('full_name') ?? '').trim()
  const email = (formData.get('email') ?? '').trim()
  const inquiry = (formData.get('inquiry') ?? '').trim()
  const message = (formData.get('message') ?? '').trim()

  if (!full_name || !email || !inquiry || !message) {
    return new Response(null, {
      status: 302,
      headers: { Location: '/index.html?status=error&reason=missing_fields' },
    })
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(email)) {
    return new Response(null, {
      status: 302,
      headers: { Location: '/index.html?status=error&reason=invalid_email' },
    })
  }

  await db.insert(formSubmissions).values({
    fullName: full_name,
    email,
    inquiry,
    message,
  })

  return new Response(null, {
    status: 302,
    headers: { Location: '/index.html?status=success' },
  })
}

export const config = {
  path: '/submit.php',
  method: 'POST',
}
