CREATE TABLE "form_submissions" (
	"id" serial PRIMARY KEY,
	"full_name" text NOT NULL,
	"email" text NOT NULL,
	"inquiry" text NOT NULL,
	"message" text NOT NULL,
	"submitted_at" timestamp DEFAULT now()
);
