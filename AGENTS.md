
# Project Development Instructions

## Project understanding
- This is an existing, actively maintained project.
- Do not assume the architecture; follow existing implementation patterns.
- Use existing documentation as the primary architectural reference.

## Before starting a task
1. Read this file.
2. Consult PROJECT_MAP.md for the project structure.
3. Identify the specific modules affected by the task.
4. Inspect only relevant source files and their dependencies.
5. Do not perform a full repository analysis unless explicitly required.
6. Do not index "3rdparty" folder

## Development principles
- Prefer minimal, targeted changes.
- Reuse existing classes, services and utilities.
- Do not introduce dependencies without approval.
- Preserve backward compatibility.
- Avoid unrelated refactoring.
- Follow existing naming and formatting conventions.

## Context efficiency
- Do not reread files whose relevant content is already available.
- Do not list or summarize the entire repository for routine tasks.
- Use targeted searches instead of reading entire directories.
- Do not repeat architectural explanations unless something changed.
- If additional exploration is necessary, explain why.

## After completing a task
- Run relevant tests and checks.
- Summarize modified files and important decisions.
- Update PROJECT_MAP.md only if architecture or module
  responsibilities have materially changed.
- Record reusable technical discoveries in project documentation.
