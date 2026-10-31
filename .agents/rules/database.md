
---
trigger: model_decision
description: "Database architecture, SQL queries, schema changes and persistence."
---

# Database Development Rules

- Reuse existing database access abstractions.
- Follow existing naming conventions.
- Do not modify database schema without considering
  backward compatibility.
- Check existing migrations before introducing changes.
- Avoid unnecessary queries and N+1 problems.
- Consider dbInstall class method as main source for database structure
