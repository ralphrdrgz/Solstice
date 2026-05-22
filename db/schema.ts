import { pgTable, serial, text, timestamp } from "drizzle-orm/pg-core";

export const formSubmissions = pgTable("form_submissions", {
  id: serial().primaryKey(),
  fullName: text("full_name").notNull(),
  email: text("email").notNull(),
  inquiry: text("inquiry").notNull(),
  message: text("message").notNull(),
  submittedAt: timestamp("submitted_at").defaultNow(),
});
