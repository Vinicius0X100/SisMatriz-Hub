---
trigger: always_on
---

# SisMatriz - Agent Instructions

## Project Philosophy

You are working on an existing production Laravel application.

Your primary goal is to preserve the project's architecture, coding standards and consistency.

Never redesign the project when the existing architecture already solves the problem.

Always behave as if you are joining an existing development team.

---

# General Rules

- Always understand the project before making changes.
- Always analyze the existing architecture.
- Always follow the current coding style.
- Never invent new architectural patterns.
- Reuse existing project conventions whenever possible.
- Keep naming conventions consistent.
- Minimize the scope of every change.
- Write clean, readable and maintainable code.
- Avoid unnecessary complexity.
- Do not introduce new libraries unless explicitly requested.

---

# Before Coding

Before writing any code, always:

1. Analyze the project structure.
2. Read existing code related to the requested feature.
3. Search for similar implementations.
4. Understand relationships between Models, Controllers, Services, Requests, Policies and Views.
5. Explain your implementation plan.
6. List every file that will be created.
7. List every existing file that will be modified.
8. Wait for approval before making any changes.

Never start implementing immediately.

---

# Existing Architecture

Always identify the module that is most similar to the requested feature.

Use it as the implementation reference.

Mirror:

- Folder organization
- Controller structure
- Model organization
- Services
- Validation
- Routes
- Blade views
- Bootstrap components
- JavaScript organization
- Naming conventions

Never create a different architecture for a new module.

---

# New Modules

When implementing a completely new module:

- Create new files only for the new functionality.
- Follow the existing folder structure exactly.
- Follow the existing architectural patterns.
- Keep the new module independent.
- Do not modify existing modules unless integration requires it.
- Reuse shared services whenever possible.

Examples of acceptable modifications:

- Registering new routes
- Adding menu entries
- Registering permissions
- Adding navigation links
- Integrating with existing shared services

Avoid modifying business logic of existing modules.

---

# Laravel Rules

Always follow existing Laravel conventions already used by the project.

Reuse existing:

- Eloquent patterns
- Form Requests
- Services
- Policies
- Middleware
- Blade layouts
- Components
- Route organization

Never duplicate business logic.

---

# Database

Never modify existing database tables unless explicitly requested.

Prefer:

- New migrations
- Foreign keys
- Existing naming conventions

Always preserve backwards compatibility.

---

# UI Rules

Reuse the existing UI.

Always:

- Use existing Bootstrap components.
- Keep spacing consistent.
- Reuse Blade layouts.
- Reuse partials.
- Follow existing colors.
- Follow existing icons.
- Follow existing tables.
- Follow existing forms.

Do not redesign the interface unless requested.

---

# Code Quality

Write code that is:

- Readable
- Maintainable
- Simple
- Consistent

Avoid:

- Duplicate code
- Dead code
- Unnecessary abstractions
- Large refactors
- Breaking changes

Only change what is necessary.

---

# Safety

Never:

- Delete files without permission.
- Rename folders without permission.
- Perform massive refactors.
- Replace existing architecture.
- Modify unrelated code.

Always explain why a change is necessary.

Always prefer incremental changes.

---

# When Unsure

If multiple implementation options exist:

- Explain the alternatives.
- Recommend the option that best matches the existing architecture.
- Ask for confirmation before proceeding.

---

# Golden Rule

The SisMatriz already has an architecture.

Your job is NOT to reinvent it.

Your job is to understand it, respect it and extend it consistently.