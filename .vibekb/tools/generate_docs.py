#!/usr/bin/env python3
"""Generate static VibeKB guide HTML into /docs from .vibekb content."""

from __future__ import annotations

import json
import re
import shutil
import sys
from dataclasses import dataclass, field
from html import escape
from pathlib import Path
from typing import Any

REPO_ROOT = Path(__file__).resolve().parents[2]
CONTENT_ROOT = REPO_ROOT / ".vibekb"
DOCS_ROOT = REPO_ROOT / "docs"
TOOLS_DIR = CONTENT_ROOT / "tools"

STATUS_VOCAB = {
    "implemented": "Implemented",
    "partial": "Partially implemented",
    "planned": "Planned",
    "experimental": "Experimental",
    "disabled": "Disabled",
    "deprecated": "Deprecated",
    "broken": "Broken",
    "unknown": "Unknown",
    "needs-verification": "Needs verification",
}

VERIFICATION_VOCAB = {
    "verified-by-test": "Verified by test",
    "verified-manually": "Verified manually",
    "verified-from-source": "Verified from source",
    "inferred-from-source": "Inferred from source",
    "reported-by-developer": "Reported by developer",
    "not-verified": "Not verified",
    "verification-failed": "Verification failed",
    "superseded": "Superseded",
    "contradicted": "Contradicted",
}

SAFETY_VOCAB = {
    "presentation-only": "Presentation only",
    "low-impact": "Low impact",
    "moderate-impact": "Moderate impact",
    "understand-dependencies-first": "Understand dependencies first",
    "high-impact": "High impact",
    "generated-or-managed": "Generated / managed elsewhere",
    "unknown": "Unknown",
}

MEMORY_TYPES = [
    "decisions",
    "constraints",
    "assumptions",
    "warnings",
    "discoveries",
    "changes",
]

SYSTEM_DOCS = [
    "mental-model",
    "components",
    "request-flow",
    "data-flow",
    "storage",
    "deployment",
]

NAV_PRIMARY = [
    ("overview", "Overview"),
    ("functionality", "Functionality"),
    ("how-it-works", "Architecture"),
    ("current-work", "Current work"),
]

NAV_SECONDARY = [
    ("data", "Data &amp; storage"),
    ("files", "Files that matter"),
    ("changes", "Changes"),
    ("why", "Decisions &amp; rationale"),
    ("handoff", "AI handoff"),
    ("reference", "Reference"),
]


def h(value: Any) -> str:
    return escape("" if value is None else str(value))


def parse_front_matter(raw: str) -> tuple[dict[str, Any], str]:
    raw = raw.lstrip("\ufeff")
    match = re.match(r"\A---\r?\n(.*?)\r?\n---\r?\n?(.*)\Z", raw, re.S)
    if not match:
        return {}, raw.strip()

    meta: dict[str, Any] = {}
    lines = match.group(1).splitlines()
    i = 0
    while i < len(lines):
        line = lines[i]
        trimmed = line.strip()
        if not trimmed or trimmed.startswith("#"):
            i += 1
            continue
        m = re.match(r"^([A-Za-z0-9_\-]+)\s*:\s*(.*)$", trimmed)
        if not m:
            i += 1
            continue
        key, value = m.group(1), m.group(2).strip()
        if value == "":
            items: list[Any] = []
            while i + 1 < len(lines) and re.match(r"^\s*-\s+(.*)$", lines[i + 1]):
                items.append(cast_scalar(re.match(r"^\s*-\s+(.*)$", lines[i + 1]).group(1).strip()))
                i += 1
            meta[key] = items if items else ""
        elif re.match(r"^\[(.*)\]$", value, re.S):
            inner = re.match(r"^\[(.*)\]$", value, re.S).group(1).strip()
            meta[key] = (
                [cast_scalar(p.strip()) for p in inner.split(",") if p.strip()]
                if inner
                else []
            )
        else:
            meta[key] = cast_scalar(value)
        i += 1
    return meta, match.group(2).strip()


def cast_scalar(value: str) -> Any:
    if value == "":
        return ""
    if (value.startswith('"') and value.endswith('"')) or (
        value.startswith("'") and value.endswith("'")
    ):
        return value[1:-1]
    lower = value.lower()
    if lower == "true":
        return True
    if lower == "false":
        return False
    if lower == "null":
        return None
    if re.match(r"^-?\d+$", value):
        return int(value)
    return value


def inline_md(text: str) -> str:
    text = escape(text)
    text = re.sub(r"`([^`]+)`", r"<code>\1</code>", text)
    text = re.sub(r"\*\*(.+?)\*\*", r"<strong>\1</strong>", text)
    text = re.sub(r"(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)", r"<em>\1</em>", text)
    text = re.sub(
        r"\[([^\]]+)\]\(([^)\s]+)\)",
        r'<a href="\2">\1</a>',
        text,
    )
    return text


def markdown_to_html(markdown: str) -> str:
    lines = markdown.replace("\r\n", "\n").replace("\r", "\n").split("\n")
    html: list[str] = []
    paragraph: list[str] = []
    list_type: str | None = None
    table_rows: list[list[str]] = []

    def flush_paragraph() -> None:
        nonlocal paragraph
        if not paragraph:
            return
        text = "\n".join(paragraph).strip()
        paragraph = []
        if text:
            html.append(f"<p>{inline_md(text)}</p>")

    def flush_list() -> None:
        nonlocal list_type
        if list_type is None:
            return
        html.append("</ol>" if list_type == "ol" else "</ul>")
        list_type = None

    def flush_table() -> None:
        nonlocal table_rows
        if not table_rows:
            return
        html.append('<div class="table-wrap"><table>')
        for index, cells in enumerate(table_rows):
            tag = "th" if index == 0 else "td"
            html.append("<tr>")
            for cell in cells:
                html.append(f"<{tag}>{inline_md(cell.strip())}</{tag}>")
            html.append("</tr>")
        html.append("</table></div>")
        table_rows = []

    for line in lines:
        table_match = re.match(r"^\s*\|(.+)\|\s*$", line)
        if table_match:
            flush_paragraph()
            flush_list()
            cells = [c.strip() for c in table_match.group(1).split("|")]
            if all(re.match(r"^:?-+:?$", c) for c in cells):
                continue
            table_rows.append(cells)
            continue
        if table_rows:
            flush_table()

        if not line.strip():
            flush_paragraph()
            flush_list()
            continue

        heading = re.match(r"^(#{1,4})\s+(.+)$", line)
        if heading:
            flush_paragraph()
            flush_list()
            level = min(len(heading.group(1)) + 1, 6)
            html.append(f"<h{level}>{inline_md(heading.group(2).strip())}</h{level}>")
            continue

        quote = re.match(r"^\s*>\s?(.*)$", line)
        if quote:
            flush_paragraph()
            flush_list()
            html.append(f"<blockquote><p>{inline_md(quote.group(1).strip())}</p></blockquote>")
            continue

        if re.match(r"^\s*(---|\*\*\*|___)\s*$", line):
            flush_paragraph()
            flush_list()
            html.append("<hr>")
            continue

        bullet = re.match(r"^\s*[-*]\s+(.+)$", line)
        if bullet:
            flush_paragraph()
            if list_type != "ul":
                flush_list()
                list_type = "ul"
                html.append("<ul>")
            html.append(f"<li>{inline_md(bullet.group(1))}</li>")
            continue

        ordered = re.match(r"^\s*\d+\.\s+(.+)$", line)
        if ordered:
            flush_paragraph()
            if list_type != "ol":
                flush_list()
                list_type = "ol"
                html.append("<ol>")
            html.append(f"<li>{inline_md(ordered.group(1))}</li>")
            continue

        flush_list()
        paragraph.append(line)

    flush_paragraph()
    flush_list()
    flush_table()
    return "\n".join(html)


@dataclass
class Record:
    meta: dict[str, Any]
    body: str
    html: str


@dataclass
class Content:
    root: Path
    manifest: dict[str, Any] = field(default_factory=dict)
    project: dict[str, Record] = field(default_factory=dict)
    functionality: dict[str, Record] = field(default_factory=dict)
    functionality_index: dict[str, Any] = field(default_factory=dict)
    system: dict[str, Record] = field(default_factory=dict)
    files: list[dict[str, Any]] = field(default_factory=list)
    memory: dict[str, dict[str, Record]] = field(default_factory=dict)
    current_work: Record | None = None
    handoff: Record | None = None
    sessions: list[Record] = field(default_factory=list)
    issues: list[dict[str, str]] = field(default_factory=list)

    def load(self) -> None:
        self.manifest = self._read_json("manifest.json")
        for name in ["identity", "intent", "current-state", "constraints"]:
            doc = self._read_markdown(f"project/{name}.md")
            if doc:
                self.project[name] = doc
        self.functionality_index = self._read_json("functionality/index.json")
        records_dir = self.root / "functionality/records"
        for path in sorted(records_dir.glob("*.md")):
            doc = self._load_record(path, "functionality")
            if not doc:
                continue
            rid = str(doc.meta.get("id", path.stem))
            if rid in self.functionality:
                self.issues.append(
                    {"level": "error", "message": f"Duplicate functionality id: {rid}"}
                )
            self.functionality[rid] = doc
        for name in SYSTEM_DOCS:
            doc = self._read_markdown(f"system/{name}.md")
            if doc:
                self.system[name] = doc
        files_data = self._read_json("files/important-files.json")
        self.files = files_data.get("files", []) if isinstance(files_data, dict) else []
        for mtype in MEMORY_TYPES:
            self.memory[mtype] = {}
            for path in sorted((self.root / "memory" / mtype).glob("*.md")):
                doc = self._load_record(path, mtype.rstrip("s"))
                if not doc:
                    continue
                rid = str(doc.meta.get("id", path.stem))
                if rid in self.memory[mtype]:
                    self.issues.append(
                        {"level": "error", "message": f"Duplicate {mtype} id: {rid}"}
                    )
                self.memory[mtype][rid] = doc
        self.current_work = self._read_markdown("work/current.md")
        self.handoff = self._read_markdown("work/handoff.md")
        for path in sorted((self.root / "work/sessions").glob("*.md")):
            doc = self._load_record(path, "session")
            if doc:
                self.sessions.append(doc)
        self.sessions.sort(
            key=lambda s: str(s.meta.get("date", "")),
            reverse=True,
        )
        self._validate()

    def _read_json(self, relative: str) -> dict[str, Any]:
        path = self.root / relative
        if not path.is_file():
            return {}
        try:
            data = json.loads(path.read_text(encoding="utf-8"))
        except json.JSONDecodeError:
            self.issues.append({"level": "error", "message": f"Malformed JSON: {relative}"})
            return {}
        return data if isinstance(data, dict) else {}

    def _read_markdown(self, relative: str) -> Record | None:
        path = self.root / relative
        if not path.is_file():
            return None
        raw = path.read_text(encoding="utf-8")
        meta, body = parse_front_matter(raw)
        return Record(meta=meta, body=body, html=markdown_to_html(body))

    def _load_record(self, path: Path, expected_type: str) -> Record | None:
        doc = self._read_markdown(str(path.relative_to(self.root)))
        if not doc:
            return None
        doc.meta.setdefault("id", path.stem)
        doc.meta.setdefault("type", expected_type)
        return doc

    def _to_list(self, value: Any) -> list[str]:
        if isinstance(value, list):
            return [str(v) for v in value if str(v)]
        if isinstance(value, str) and value:
            return [value]
        return []

    def _validate(self) -> None:
        for rid, rec in self.functionality.items():
            meta = rec.meta
            for req in ["id", "title", "status", "summary"]:
                if not meta.get(req):
                    self.issues.append(
                        {
                            "level": "error",
                            "message": f"Functionality '{rid}' missing required field: {req}",
                        }
                    )
            status = str(meta.get("status", ""))
            if status and status not in STATUS_VOCAB:
                self.issues.append(
                    {
                        "level": "error",
                        "message": f"Functionality '{rid}' has unknown status: {status}",
                    }
                )
            ver = str(meta.get("verification", ""))
            if ver and ver not in VERIFICATION_VOCAB:
                self.issues.append(
                    {
                        "level": "error",
                        "message": f"Functionality '{rid}' has unknown verification: {ver}",
                    }
                )
            for dep in self._to_list(meta.get("depends_on")):
                if dep not in self.functionality:
                    self.issues.append(
                        {
                            "level": "error",
                            "message": f"Functionality '{rid}' depends on unknown functionality: {dep}",
                        }
                    )
            for ref in self.resolve_memory(meta.get("related_memory")):
                if not ref["resolved"]:
                    self.issues.append(
                        {
                            "level": "warn",
                            "message": f"Functionality '{rid}' references unresolved memory: {ref['id']}",
                        }
                    )
        for mtype, records in self.memory.items():
            for rid, rec in records.items():
                for fn in self._to_list(rec.meta.get("functionality")):
                    if fn not in self.functionality:
                        self.issues.append(
                            {
                                "level": "warn",
                                "message": f"{mtype.capitalize()} '{rid}' links to unknown functionality: {fn}",
                            }
                        )
        for file in self.files:
            path = str(file.get("path", "(unnamed)"))
            safety = str(file.get("safety", ""))
            if safety and safety not in SAFETY_VOCAB:
                self.issues.append(
                    {
                        "level": "error",
                        "message": f"File '{path}' has unknown safety level: {safety}",
                    }
                )
            for fn in self._to_list(file.get("functionality")):
                if fn not in self.functionality:
                    self.issues.append(
                        {
                            "level": "warn",
                            "message": f"File '{path}' links to unknown functionality: {fn}",
                        }
                    )

    def project_name(self) -> str:
        identity = self.project.get("identity")
        if identity and identity.meta.get("title"):
            return str(identity.meta["title"])
        return "Software"

    def functionality_groups(self) -> list[dict[str, Any]]:
        group_defs = self.functionality_index.get("groups", [])
        order = self.functionality_index.get("order", [])
        rank = {str(v): i for i, v in enumerate(order)}
        records = sorted(
            self.functionality.values(),
            key=lambda r: (
                rank.get(str(r.meta.get("id", "")), 999),
                str(r.meta.get("title", "")),
            ),
        )
        groups: list[dict[str, Any]] = []
        group_index: dict[str, int] = {}
        for g in group_defs:
            if not isinstance(g, dict):
                continue
            gid = str(g.get("id", ""))
            if not gid:
                continue
            group_index[gid] = len(groups)
            groups.append(
                {
                    "id": gid,
                    "title": str(g.get("title", gid.replace("-", " ").title())),
                    "description": str(g.get("description", "")),
                    "records": [],
                }
            )
        for rec in records:
            area = str(rec.meta.get("area", "other"))
            if area not in group_index:
                group_index[area] = len(groups)
                groups.append(
                    {
                        "id": area,
                        "title": area.replace("-", " ").title(),
                        "description": "",
                        "records": [],
                    }
                )
            groups[group_index[area]]["records"].append(rec)
        return [g for g in groups if g["records"]]

    def status_counts(self) -> dict[str, int]:
        counts: dict[str, int] = {}
        for rec in self.functionality.values():
            status = str(rec.meta.get("status", "unknown"))
            counts[status] = counts.get(status, 0) + 1
        return counts

    def resolve_functionality(self, ids: Any) -> list[dict[str, Any]]:
        out = []
        for fid in self._to_list(ids):
            rec = self.functionality.get(fid)
            out.append(
                {
                    "id": fid,
                    "title": str(rec.meta.get("title", fid)) if rec else fid,
                    "resolved": rec is not None,
                }
            )
        return out

    def resolve_memory(self, refs: Any) -> list[dict[str, Any]]:
        out = []
        for ref in self._to_list(refs):
            if ":" not in ref:
                out.append({"type": "", "id": ref, "title": ref, "resolved": False})
                continue
            singular, mid = ref.split(":", 1)
            mtype = plural_memory_type(singular)
            rec = self.memory.get(mtype, {}).get(mid)
            out.append(
                {
                    "type": mtype,
                    "id": mid,
                    "title": str(rec.meta.get("title", mid)) if rec else ref,
                    "resolved": rec is not None,
                }
            )
        return out

    def dependents_of(self, fid: str) -> list[dict[str, Any]]:
        out = []
        for rid, rec in self.functionality.items():
            if fid in self._to_list(rec.meta.get("depends_on")):
                out.append(
                    {
                        "id": rid,
                        "title": str(rec.meta.get("title", rid)),
                        "resolved": True,
                    }
                )
        return out

    def files_for_functionality(self, fid: str) -> list[dict[str, Any]]:
        return [
            f
            for f in self.files
            if fid in self._to_list(f.get("functionality"))
        ]


def plural_memory_type(singular: str) -> str:
    singular = singular.lower().strip()
    mapping = {
        "decision": "decisions",
        "decisions": "decisions",
        "constraint": "constraints",
        "constraints": "constraints",
        "assumption": "assumptions",
        "assumptions": "assumptions",
        "warning": "warnings",
        "warnings": "warnings",
        "discovery": "discoveries",
        "discoveries": "discoveries",
        "change": "changes",
        "changes": "changes",
    }
    return mapping.get(singular, singular)


def status_tone(status: str) -> str:
    return {
        "implemented": "ok",
        "partial": "warn",
        "experimental": "warn",
        "needs-verification": "warn",
        "planned": "info",
        "disabled": "muted",
        "deprecated": "muted",
        "broken": "danger",
    }.get(status, "unknown")


def verification_tone(ver: str) -> str:
    return {
        "verified-by-test": "ok",
        "verified-manually": "ok",
        "verified-from-source": "ok",
        "inferred-from-source": "info",
        "reported-by-developer": "info",
        "not-verified": "warn",
        "needs-verification": "warn",
        "verification-failed": "danger",
        "contradicted": "danger",
        "superseded": "danger",
    }.get(ver, "unknown")


def safety_tone(safety: str) -> str:
    return {
        "presentation-only": "ok",
        "low-impact": "ok",
        "moderate-impact": "info",
        "understand-dependencies-first": "warn",
        "high-impact": "danger",
    }.get(safety, "unknown")


def severity_tone(severity: str) -> str:
    return {
        "critical": "danger",
        "high": "danger",
        "medium": "warn",
        "low": "info",
    }.get(severity.lower(), "unknown")


def badge(label: str, tone: str) -> str:
    return f'<span class="badge badge--{h(tone)}">{h(label)}</span>'


def status_badge(status: str) -> str:
    return badge(STATUS_VOCAB.get(status, status.replace("-", " ").title()), status_tone(status))


def verification_badge(ver: str) -> str:
    return badge(
        VERIFICATION_VOCAB.get(ver, ver.replace("-", " ").title()),
        verification_tone(ver),
    )


class SiteGenerator:
    def __init__(self, content: Content) -> None:
        self.content = content
        self.project_name = content.project_name()
        self.pages: dict[str, str] = {}

    def rel(self, from_page: str, to_page: str) -> str:
        from_parts = Path(from_page).parent.parts
        to_parts = Path(to_page).parts
        shared = 0
        for left, right in zip(from_parts, to_parts, strict=False):
            if left != right:
                break
            shared += 1
        ups = [".."] * (len(from_parts) - shared)
        down = list(to_parts[shared:])
        return "/".join(ups + down) if ups or down else to_page

    def register(self, page_path: str, body: str, view: str, title: str) -> None:
        self.pages[page_path] = self._layout(page_path, body, view, title)

    def write_all(self) -> None:
        if DOCS_ROOT.exists():
            shutil.rmtree(DOCS_ROOT)
        DOCS_ROOT.mkdir(parents=True)
        assets = DOCS_ROOT / "assets"
        (assets / "css").mkdir(parents=True)
        (assets / "js").mkdir(parents=True)
        (assets / "data").mkdir(parents=True)
        shutil.copy2(TOOLS_DIR / "guide.css", assets / "css" / "guide.css")
        shutil.copy2(TOOLS_DIR / "guide.js", assets / "js" / "guide.js")
        self._write_search_index(assets / "data" / "search.json")
        for rel_path, html in self.pages.items():
            out = DOCS_ROOT / rel_path
            out.parent.mkdir(parents=True, exist_ok=True)
            out.write_text(html, encoding="utf-8")

    def _asset_href(self, page_path: str, asset_rel: str) -> str:
        return self.rel(page_path, f"assets/{asset_rel}")

    def _page_href(self, from_page: str, to_page: str) -> str:
        return self.rel(from_page, to_page)

    def _nav_link(self, from_page: str, view: str, label: str, current_view: str) -> str:
        to_page = view_to_page(view)
        active = current_view == view or (
            current_view == "functionality-detail" and view == "functionality"
        )
        attrs = ' aria-current="page"' if active else ""
        return (
            f'<li><a href="{h(self._page_href(from_page, to_page))}"{attrs}>'
            f"{label}</a></li>"
        )

    def _layout(self, page_path: str, body: str, view: str, title: str) -> str:
        css = self._asset_href(page_path, "css/guide.css")
        js = self._asset_href(page_path, "js/guide.js")
        nav_primary = "".join(
            self._nav_link(page_path, v, label, view) for v, label in NAV_PRIMARY
        )
        nav_secondary = "".join(
            self._nav_link(page_path, v, label, view) for v, label in NAV_SECONDARY
        )
        search_href = self._page_href(page_path, "search/index.html")
        return f"""<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{h(title)} · {h(self.project_name)} — VibeKB</title>
  <meta name="description" content="A living explanation of what {h(self.project_name)} currently does.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{h(css)}">
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<div class="app-shell">
  <header class="site-header">
    <div class="site-header__inner">
      <a class="brand" href="{h(self._page_href(page_path, 'index.html'))}">
        <span class="brand__mark">VibeKB</span>
        <span class="brand__project">{h(self.project_name)}</span>
      </a>
      <form class="site-search" role="search" action="{h(search_href)}">
        <label class="visually-hidden" for="site-search-input">Search guide</label>
        <input id="site-search-input" name="q" type="search" placeholder="Search guide…" autocomplete="off">
      </form>
      <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="guide-sidebar" hidden>Menu</button>
    </div>
  </header>
  <div class="nav-backdrop" id="nav-backdrop" hidden></div>
  <aside class="sidebar" id="guide-sidebar" aria-label="Guide navigation">
    <nav class="sidebar-nav" aria-label="Guide sections">
      <p class="sidebar-nav__label">Primary</p>
      <ul class="sidebar-nav__list">{nav_primary}</ul>
      <p class="sidebar-nav__label">Explore</p>
      <ul class="sidebar-nav__list sidebar-nav__list--secondary">{nav_secondary}</ul>
    </nav>
  </aside>
  <div class="app-main">
    <main id="main" class="wrap">{body}</main>
    <footer class="site-footer">
      <div class="wrap site-footer__inner">
        <p><strong>VibeKB</strong> — Understand what your software is doing.</p>
        <p class="muted">Generated from repository-owned content in <code>.vibekb/</code>.</p>
      </div>
    </footer>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="{h(js)}" defer></script>
</body>
</html>"""

    def functionality_chips(self, from_page: str, items: list[dict[str, Any]]) -> str:
        if not items:
            return '<span class="muted">None.</span>'
        out = []
        for it in items:
            if it["resolved"]:
                href = self._page_href(from_page, f"functionality/{it['id']}.html")
                out.append(f'<a class="chip" href="{h(href)}">{h(it["title"])}</a>')
            else:
                out.append(
                    f'<span class="chip chip--broken" title="Unresolved reference">'
                    f'{h(it["title"])} ⚠</span>'
                )
        return " ".join(out)

    def memory_chips(self, from_page: str, items: list[dict[str, Any]]) -> str:
        if not items:
            return '<span class="muted">None.</span>'
        out = []
        for it in items:
            if it["resolved"]:
                href = self._page_href(
                    from_page, f"why/{it['type']}/{it['id']}.html"
                )
                out.append(f'<a class="chip" href="{h(href)}">{h(it["title"])}</a>')
            else:
                out.append(
                    f'<span class="chip chip--broken" title="Unresolved reference">'
                    f'{h(it["title"])} ⚠</span>'
                )
        return " ".join(out)

    def file_chips(self, paths: Any) -> str:
        items = paths if isinstance(paths, list) else ([paths] if paths else [])
        if not items:
            return '<span class="muted">None.</span>'
        return " ".join(
            f'<code class="chip chip--file">{h(str(p))}</code>' for p in items
        )

    def generate(self) -> None:
        self._gen_overview()
        self._gen_functionality_index()
        for fid in self.content.functionality:
            self._gen_functionality_detail(fid)
        self._gen_how_it_works()
        self._gen_data()
        self._gen_files()
        self._gen_current_work()
        self._gen_changes()
        self._gen_why_index()
        for mtype, records in self.content.memory.items():
            for rid in records:
                self._gen_why_detail(mtype, rid)
        self._gen_handoff()
        self._gen_reference()
        self._gen_search()
        self.write_all()

    def _gen_overview(self) -> None:
        page = "index.html"
        c = self.content
        identity = c.project.get("identity")
        current_state = c.project.get("current-state")
        status_counts = c.status_counts()
        groups = c.functionality_groups()
        warnings = c.memory.get("warnings", {})
        work = c.current_work
        handoff = c.handoff
        mental_model = c.system.get("mental-model")
        storage = c.system.get("storage")
        one_liner = ""
        if identity:
            one_liner = str(
                identity.meta.get("one_liner") or identity.meta.get("summary") or ""
            )
        identity_summary = str(identity.meta.get("summary", "")) if identity else ""
        current_summary = (
            str(current_state.meta.get("summary", "")) if current_state else ""
        )
        total = sum(status_counts.values())
        badges = []
        for key, label in STATUS_VOCAB.items():
            if status_counts.get(key):
                badges.append(badge(f"{label} · {status_counts[key]}", status_tone(key)))
        area_cards = []
        for group in groups:
            count = len(group["records"])
            group_statuses: dict[str, int] = {}
            for rec in group["records"]:
                st = str(rec.meta.get("status", "unknown"))
                group_statuses[st] = group_statuses.get(st, 0) + 1
            samples = group["records"][:2]
            status_badges = []
            for key, label in STATUS_VOCAB.items():
                if group_statuses.get(key):
                    status_badges.append(
                        badge(f"{label} · {group_statuses[key]}", status_tone(key))
                    )
            sample_items = "".join(
                f'<li><a href="{h(self._page_href(page, f"functionality/{rec.meta.get("id")}.html"))}">'
                f'{h(rec.meta.get("title", rec.meta.get("id")))}</a></li>'
                for rec in samples
            )
            area_url = self._page_href(page, f"functionality/index.html#{group['id']}")
            area_cards.append(
                f"""<li class="area-summary-card">
  <div class="area-summary-card__head">
    <h3 class="area-summary-card__title"><a href="{h(area_url)}">{h(group['title'])}</a></h3>
    <p class="area-summary-card__count">{count} record{'s' if count != 1 else ''}</p>
  </div>
  {f'<p class="area-summary-card__desc">{h(group["description"])}</p>' if group['description'] else ''}
  <p class="badge-row badge-row--quiet">{' '.join(status_badges)}</p>
  {f'<ul class="area-summary-card__samples">{sample_items}</ul>' if samples else ''}
  <p class="area-summary-card__action"><a class="text-link" href="{h(area_url)}">View this area →</a></p>
</li>"""
            )
        warn_items = []
        for wid, w in warnings.items():
            href = self._page_href(page, f"why/warnings/{wid}.html")
            warn_items.append(
                f'<li><a href="{h(href)}">{h(w.meta.get("title", wid))}</a> '
                f'{badge(str(w.meta.get("severity", "severity")).title(), severity_tone(str(w.meta.get("severity", ""))))}</li>'
            )
        work_block = '<p class="muted">No active AI work recorded.</p>'
        if work:
            work_block = (
                f'<p><strong>{h(work.meta.get("title", "Current work"))}</strong> '
                f'{badge(str(work.meta.get("status", "unknown")).replace("-", " "), "info")}</p>'
                f'<p>{h(work.meta.get("summary", ""))}</p>'
                f'<p><a class="text-link" href="{h(self._page_href(page, "current-work/index.html"))}">See current work →</a></p>'
            )
        next_text = (
            str(handoff.meta.get("summary", ""))
            if handoff and handoff.meta.get("summary")
            else "Start with the functionality index, then read the architecture."
        )
        body = f"""<article class="view view-overview">
<header class="page-head reading-column">
  <p class="eyebrow">Software overview</p>
  <h1>{h(self.project_name)}</h1>
  {f'<p class="lede">{h(one_liner)}</p>' if one_liner else ''}
</header>
<section class="snapshot-bar wide-section" aria-label="At a glance">
  <div class="snapshot-bar__item">
    <p class="snapshot-bar__label">Tracked functionality</p>
    <p class="snapshot-bar__value">{total} area{'s' if total != 1 else ''}</p>
    <p class="badge-row">{' '.join(badges)}</p>
  </div>
  <div class="snapshot-bar__item">
    <p class="snapshot-bar__label">Storage</p>
    <p class="snapshot-bar__text">{h(storage.meta.get('summary', 'See the data and storage view.') if storage else 'See the data and storage view.')}</p>
    <p><a class="text-link" href="{h(self._page_href(page, 'data/index.html'))}">Data &amp; storage →</a></p>
  </div>
  <div class="snapshot-bar__item">
    <p class="snapshot-bar__label">Last meaningful update</p>
    <p class="snapshot-bar__value snapshot-bar__value--quiet">{h(current_state.meta.get('updated', 'unknown') if current_state else 'unknown')}</p>
  </div>
</section>
<section class="content-section reading-column" aria-labelledby="ov-what">
  <header class="section-intro"><h2 id="ov-what">What this software does</h2></header>
  {f'<p>{h(identity_summary)}</p>' if identity_summary else ''}
  {f'<p class="text-soft">{h(current_summary)}</p>' if current_summary and current_summary != identity_summary else ''}
  <p><a class="text-link" href="{h(self._page_href(page, 'functionality/index.html'))}">Browse the full functionality index →</a></p>
</section>
<section class="content-section reading-column" aria-labelledby="ov-think">
  <header class="section-intro"><h2 id="ov-think">How to think about it</h2></header>
  {f'<p>{h(mental_model.meta.get("summary", ""))}</p>' if mental_model and mental_model.meta.get("summary") else '<p class="muted">No mental model recorded yet.</p>'}
  <p><a class="text-link" href="{h(self._page_href(page, 'how-it-works/index.html'))}">Read the architecture →</a></p>
</section>
<section class="content-section wide-section" aria-labelledby="ov-areas">
  <header class="section-intro reading-column">
    <h2 id="ov-areas">Functional areas</h2>
    <p class="section-intro__support">A compact map of the system. Open any area for its records.</p>
  </header>
  <ul class="area-summary-list">{''.join(area_cards)}</ul>
</section>
<div class="split content-section">
  <section aria-labelledby="ov-warn" class="callout callout--warn">
    <h2 id="ov-warn">Active warnings</h2>
    {('<ul class="callout-list">' + ''.join(warn_items) + '</ul>') if warn_items else '<p class="muted">No active warnings recorded.</p>'}
  </section>
  <section aria-labelledby="ov-work" class="callout callout--work">
    <h2 id="ov-work">Current AI work</h2>
    {work_block}
  </section>
</div>
<section class="next-step content-section" aria-labelledby="ov-next">
  <h2 id="ov-next">Recommended starting point</h2>
  <p>{h(next_text)}</p>
  <p class="button-row">
    <a class="btn btn--primary" href="{h(self._page_href(page, 'functionality/index.html'))}">Explore functionality</a>
    <a class="btn" href="{h(self._page_href(page, 'handoff/index.html'))}">Read the AI handoff</a>
  </p>
</section>
</article>"""
        self.register(page, body, "overview", "Overview")

    def _gen_functionality_index(self) -> None:
        page = "functionality/index.html"
        groups = self.content.functionality_groups()
        sections = []
        for group in groups:
            rows = []
            for rec in group["records"]:
                m = rec.meta
                fid = str(m.get("id"))
                detail = self._page_href(page, f"functionality/{fid}.html")
                ver = ""
                if m.get("verification"):
                    ver = (
                        f'<div><dt>Verification</dt><dd>{verification_badge(str(m["verification"]))}</dd></div>'
                    )
                trigger = ""
                if m.get("trigger"):
                    trigger = f'<div><dt>Trigger</dt><dd>{h(m["trigger"])}</dd></div>'
                rows.append(
                    f"""<li class="record-card" data-status="{h(m.get('status', ''))}" data-area="{h(group['id'])}" data-verification="{h(m.get('verification', ''))}" data-facing="{'user' if m.get('user_facing') else 'system'}">
  <div class="record-card__row">
    <h3 class="record-card__title"><a class="record-card__link" href="{h(detail)}">{h(m.get('title', fid))}</a></h3>
    <div class="record-card__status">{status_badge(str(m.get('status', 'unknown')))}</div>
  </div>
  <p class="record-card__summary">{h(m.get('summary', ''))}</p>
  <dl class="record-card__meta">{ver}{trigger}
    <div><dt>Facing</dt><dd>{'User-facing' if m.get('user_facing') else 'System'}</dd></div>
    <div><dt>Updated</dt><dd>{h(m.get('updated', 'unknown'))}</dd></div>
  </dl>
</li>"""
                )
            sections.append(
                f"""<section class="group-block wide-section" id="{h(group['id'])}" aria-labelledby="grp-{h(group['id'])}">
  <header class="section-intro">
    <h2 id="grp-{h(group['id'])}">{h(group['title'])}</h2>
    {f'<p class="section-intro__support">{h(group["description"])}</p>' if group['description'] else ''}
  </header>
  <ul class="record-list">{''.join(rows)}</ul>
</section>"""
            )
        status_opts = "".join(
            f'<option value="{h(k)}">{h(v)}</option>' for k, v in STATUS_VOCAB.items()
        )
        area_opts = "".join(
            f'<option value="{h(g["id"])}">{h(g["title"])}</option>' for g in groups
        )
        ver_opts = "".join(
            f'<option value="{h(k)}">{h(v)}</option>' for k, v in VERIFICATION_VOCAB.items()
        )
        body = f"""<article class="view view-func-index">
<header class="page-head reading-column">
  <p class="eyebrow">Functionality index</p>
  <h1>Everything the software does</h1>
  <p class="lede">Functionality is the primary unit. Each item is something the software does, with its real status and how it was verified.</p>
</header>
<form class="filters wide-section" id="functionality-filters">
  <div class="filters__row">
    <label>Status<select name="status"><option value="">Any</option>{status_opts}</select></label>
    <label>Area<select name="area"><option value="">Any</option>{area_opts}</select></label>
    <label>Verification<select name="verification"><option value="">Any</option>{ver_opts}</select></label>
    <label>Facing<select name="facing"><option value="">Any</option><option value="user">User-facing</option><option value="system">System</option></select></label>
    <div class="filters__actions"><button type="button" class="btn" id="clear-filters">Clear</button></div>
  </div>
</form>
{''.join(sections)}
<p class="empty-state" id="filter-empty" hidden>No functionality matches these filters.</p>
</article>"""
        self.register(page, body, "functionality", "Functionality")

    def _gen_functionality_detail(self, fid: str) -> None:
        page = f"functionality/{fid}.html"
        rec = self.content.functionality[fid]
        m = rec.meta
        deps = self.content.resolve_functionality(m.get("depends_on"))
        dependents = self.content.dependents_of(fid)
        mem = self.content.resolve_memory(m.get("related_memory"))
        file_records = self.content.files_for_functionality(fid)
        reads = m.get("reads", []) if isinstance(m.get("reads"), list) else []
        writes = m.get("writes", []) if isinstance(m.get("writes"), list) else []
        config = m.get("config", [])
        primary_files = m.get("files", [])
        file_list = ""
        if file_records:
            items = []
            for f in file_records:
                href = self._page_href(page, f"files/index.html#{f.get('path', '')}")
                items.append(
                    f'<li><a href="{h(href)}"><code>{h(f.get("path", ""))}</code></a> '
                    f'{badge(SAFETY_VOCAB.get(str(f.get("safety", "")), str(f.get("safety", "unknown"))), safety_tone(str(f.get("safety", ""))))}</li>'
                )
            file_list = f'<ul class="rail-list">{"".join(items)}</ul>'
        else:
            file_list = f"<p>{self.file_chips(primary_files)}</p>"
        body = f"""<article class="view view-func-detail">
<p class="breadcrumb"><a href="{h(self._page_href(page, 'functionality/index.html'))}">← All functionality</a></p>
<header class="page-head reading-column">
  <p class="eyebrow">{h(str(m.get('area', 'functionality')).replace('-', ' ').title())}</p>
  <h1>{h(m.get('title', fid))}</h1>
  <p class="lede">{h(m.get('summary', ''))}</p>
  <div class="badge-row">
    {status_badge(str(m.get('status', 'unknown')))}
    {verification_badge(str(m['verification'])) if m.get('verification') else ''}
    {badge('User-facing' if m.get('user_facing') else 'System', 'info' if m.get('user_facing') else 'muted')}
  </div>
</header>
<div class="detail-grid">
  <div class="detail-main">
    {f'<div class="prose reading-column">{rec.html}</div>' if rec.html else '<p class="muted">No narrative recorded for this functionality yet.</p>'}
  </div>
  <aside class="detail-rail" aria-label="Record metadata">
    <div class="rail-card metadata-group">
      <h2>Status</h2>
      <dl class="rail-dl">
        <dt>Status</dt><dd>{status_badge(str(m.get('status', 'unknown')))}</dd>
        {f'<dt>Verification</dt><dd>{verification_badge(str(m["verification"]))}</dd>' if m.get('verification') else ''}
        <dt>Updated</dt><dd>{h(m.get('updated', 'unknown'))}</dd>
        <dt>Facing</dt><dd>{'User-facing' if m.get('user_facing') else 'System'}</dd>
      </dl>
    </div>
    {f'<div class="rail-card metadata-group"><h2>Trigger</h2><p>{h(m["trigger"])}</p></div>' if m.get('trigger') else ''}
    <div class="rail-card metadata-group">
      <h2>Data read or written</h2>
      <p><strong>Reads:</strong> {self.file_chips(reads)}</p>
      <p><strong>Writes:</strong> {self.file_chips(writes)}</p>
      {f'<p><strong>Config:</strong> {self.file_chips(config)}</p>' if config else ''}
    </div>
    <div class="rail-card metadata-group"><h2>Files involved</h2>{file_list}</div>
    <div class="rail-card metadata-group">
      <h2>Dependencies</h2>
      <p><strong>Depends on:</strong> {self.functionality_chips(page, deps)}</p>
      <p><strong>Depended on by:</strong> {self.functionality_chips(page, dependents)}</p>
    </div>
    <div class="rail-card metadata-group">
      <h2>Related rationale</h2>
      <p>{self.memory_chips(page, mem)}</p>
    </div>
  </aside>
</div>
</article>"""
        self.register(page, body, "functionality-detail", str(m.get("title", fid)))

    def _gen_how_it_works(self) -> None:
        page = "how-it-works/index.html"
        order = [
            ("mental-model", "The simplest mental model"),
            ("components", "Major components"),
            ("request-flow", "The request lifecycle"),
            ("deployment", "Deployment"),
        ]
        sections = []
        for name, fallback in order:
            doc = self.content.system.get(name)
            if not doc:
                continue
            sections.append(
                f"""<section class="doc-section content-section" aria-labelledby="hiw-{h(name)}">
  <header class="section-intro reading-column">
    <h2 id="hiw-{h(name)}">{h(doc.meta.get('title', fallback))}</h2>
  </header>
  <div class="prose reading-column">{doc.html}</div>
</section>"""
            )
        body = f"""<article class="view view-doc">
<header class="page-head reading-column">
  <p class="eyebrow">Architecture</p>
  <h1>How the software works</h1>
  <p class="lede">A paced, system-level explanation — the mental model first, then the parts, lifecycle, and deployment.</p>
</header>
{''.join(sections)}
<nav class="cross-links" aria-label="Related views">
  <a class="btn" href="{h(self._page_href(page, 'data/index.html'))}">Data &amp; storage →</a>
  <a class="btn" href="{h(self._page_href(page, 'files/index.html'))}">Files that matter →</a>
</nav>
</article>"""
        self.register(page, body, "how-it-works", "Architecture")

    def _gen_data(self) -> None:
        page = "data/index.html"
        storage = self.content.system.get("storage")
        data_flow = self.content.system.get("data-flow")
        store_use: dict[str, dict[str, list[dict[str, str]]]] = {}
        for rid, rec in self.content.functionality.items():
            m = rec.meta
            title = str(m.get("title", rid))
            for store in m.get("reads", []) if isinstance(m.get("reads"), list) else []:
                store_use.setdefault(str(store), {}).setdefault("reads", []).append(
                    {"id": rid, "title": title}
                )
            for store in m.get("writes", []) if isinstance(m.get("writes"), list) else []:
                store_use.setdefault(str(store), {}).setdefault("writes", []).append(
                    {"id": rid, "title": title}
                )
        rows = []
        for store in sorted(store_use):
            use = store_use[store]
            write_chips = " ".join(
                f'<a class="chip" href="{h(self._page_href(page, f"functionality/{f["id"]}.html"))}">{h(f["title"])}</a>'
                for f in use.get("writes", [])
            ) or '<span class="muted">—</span>'
            read_chips = " ".join(
                f'<a class="chip" href="{h(self._page_href(page, f"functionality/{f["id"]}.html"))}">{h(f["title"])}</a>'
                for f in use.get("reads", [])
            ) or '<span class="muted">—</span>'
            rows.append(
                f"<tr><td><code>{h(store)}</code></td><td>{write_chips}</td><td>{read_chips}</td></tr>"
            )
        body = f"""<article class="view view-doc">
<header class="page-head reading-column">
  <p class="eyebrow">Data &amp; storage</p>
  <h1>What the software stores</h1>
  <p class="lede">Where data comes from, where it goes, and what it means to the application.</p>
</header>
{f'<section class="doc-section content-section"><div class="prose reading-column">{storage.html}</div></section>' if storage else ''}
{f'<section class="doc-section content-section"><header class="section-intro reading-column"><h2>{h(data_flow.meta.get("title", "How data flows"))}</h2></header><div class="prose reading-column">{data_flow.html}</div></section>' if data_flow else ''}
<section class="doc-section content-section wide-section" aria-labelledby="data-use">
  <header class="section-intro reading-column"><h2 id="data-use">Which functionality touches each store</h2></header>
  {'<div class="table-wrap"><table><tr><th>Store</th><th>Written by</th><th>Read by</th></tr>' + ''.join(rows) + '</table></div>' if rows else '<p class="muted">No data stores are declared by functionality records.</p>'}
</section>
</article>"""
        self.register(page, body, "data", "Data & storage")

    def _gen_files(self) -> None:
        page = "files/index.html"
        cards = []
        for f in self.content.files:
            fn_chips = []
            for fid in f.get("functionality", []) if isinstance(f.get("functionality"), list) else []:
                rec = self.content.functionality.get(str(fid))
                if rec:
                    href = self._page_href(page, f"functionality/{fid}.html")
                    fn_chips.append(
                        f'<a class="chip" href="{h(href)}">{h(rec.meta.get("title", fid))}</a>'
                    )
                else:
                    fn_chips.append(f'<span class="chip chip--broken">{h(str(fid))} ⚠</span>')
            cards.append(
                f"""<section class="file-card" id="{h(f.get('path', ''))}">
  <div class="file-card__head">
    <h2><code>{h(f.get('path', 'unknown'))}</code></h2>
    <div class="file-card__badges">
      {badge(SAFETY_VOCAB.get(str(f.get('safety', 'unknown')), str(f.get('safety', 'unknown'))), safety_tone(str(f.get('safety', ''))))}
      {verification_badge(str(f['provenance'])) if f.get('provenance') else ''}
    </div>
  </div>
  <p class="file-card__purpose">{h(f.get('purpose', ''))}</p>
  <dl class="file-card__meta">
    {f'<div><dt>Runs when</dt><dd>{h(f.get("runs_when", ""))}</dd></div>' if f.get('runs_when') else ''}
    {f'<div><dt>Implements</dt><dd>{"".join(fn_chips)}</dd></div>' if fn_chips else ''}
    {f'<div><dt>Depends on</dt><dd>{self.file_chips(f.get("depends_on"))}</dd></div>' if f.get('depends_on') else ''}
    {f'<div><dt>Test after change</dt><dd>{h(f.get("test_after_change", ""))}</dd></div>' if f.get('test_after_change') else ''}
  </dl>
</section>"""
            )
        body = f"""<article class="view view-files">
<header class="page-head reading-column">
  <p class="eyebrow">Files that matter</p>
  <h1>The files worth understanding</h1>
  <p class="lede">A curated list — not the whole repository. Each file says what it does, when it runs, and how safe it is to change.</p>
</header>
<div class="file-list">{''.join(cards) if cards else '<p class="empty-state">No important files recorded.</p>'}</div>
</article>"""
        self.register(page, body, "files", "Files that matter")

    def _gen_current_work(self) -> None:
        page = "current-work/index.html"
        work = self.content.current_work
        if work:
            m = work.meta
            body_inner = f"""<section class="work-summary callout callout--work">
  <h2>{h(m.get('title', 'Current work'))}</h2>
  <div class="badge-row">
    {badge(str(m.get('status', 'unknown')).replace('-', ' '), 'info')}
    {verification_badge(str(m['verification_state'])) if m.get('verification_state') else ''}
  </div>
  <dl class="rail-dl work-meta">
    {f'<dt>Objective</dt><dd>{h(m.get("objective", ""))}</dd>' if m.get('objective') else ''}
    {f'<dt>Affected functionality</dt><dd>{self.functionality_chips(page, self.content.resolve_functionality(m.get("affected_functionality")))}</dd>' if m.get('affected_functionality') else ''}
    {f'<dt>Expected files</dt><dd>{self.file_chips(m.get("expected_files"))}</dd>' if m.get('expected_files') else ''}
    {f'<dt>Data impact</dt><dd>{h(m.get("data_impact", ""))}</dd>' if m.get('data_impact') else ''}
  </dl>
</section>
<section class="doc-section content-section"><div class="prose reading-column">{work.html}</div></section>"""
        else:
            body_inner = '<p class="empty-state">No active AI work is recorded.</p>'
        body = f"""<article class="view view-doc">
<header class="page-head reading-column">
  <p class="eyebrow">Current work</p>
  <h1>What AI is changing right now</h1>
  <p class="lede">The active development objective, its impact, and how far along it is.</p>
</header>
{body_inner}
<nav class="cross-links" aria-label="Related views">
  <a class="btn" href="{h(self._page_href(page, 'handoff/index.html'))}">AI handoff →</a>
  <a class="btn" href="{h(self._page_href(page, 'changes/index.html'))}">Completed changes →</a>
</nav>
</article>"""
        self.register(page, body, "current-work", "Current work")

    def _gen_changes(self) -> None:
        page = "changes/index.html"
        changes = list(self.content.memory.get("changes", {}).items())
        changes.sort(
            key=lambda item: str(item[1].meta.get("updated", "")),
            reverse=True,
        )
        cards = []
        for cid, c in changes:
            m = c.meta
            cards.append(
                f"""<li class="change-card">
  <div class="change-card__head">
    <h2>{h(m.get('title', cid))}</h2>
    <div class="badge-row">
      {badge(str(m.get('status', '')), 'info') if m.get('status') else ''}
      {verification_badge(str(m['verification'])) if m.get('verification') else ''}
      <span class="muted">{h(m.get('updated', ''))}</span>
    </div>
  </div>
  <p>{h(m.get('summary', ''))}</p>
  <div class="prose reading-column change-card__body">{c.html}</div>
  {f'<p class="change-card__links"><strong>Affected:</strong> {self.functionality_chips(page, self.content.resolve_functionality(m.get("functionality")))}</p>' if m.get('functionality') else ''}
</li>"""
            )
        body = f"""<article class="view view-changes">
<header class="page-head reading-column">
  <p class="eyebrow">Changes</p>
  <h1>What changed, and why it matters</h1>
  <p class="lede">Meaningful shifts in what the software does — not a log of every edit.</p>
</header>
{'<ol class="change-list">' + ''.join(cards) + '</ol>' if cards else '<p class="empty-state">No changes recorded yet.</p>'}
</article>"""
        self.register(page, body, "changes", "Changes")

    def _gen_why_index(self) -> None:
        page = "why/index.html"
        type_labels = {
            "decisions": "Decisions",
            "constraints": "Constraints",
            "assumptions": "Assumptions",
            "warnings": "Warnings",
            "discoveries": "Discoveries",
            "changes": "Changes",
        }
        sections = []
        for mtype, label in type_labels.items():
            records = self.content.memory.get(mtype, {})
            if not records:
                continue
            items = []
            for rid, rec in records.items():
                m = rec.meta
                href = self._page_href(page, f"why/{mtype}/{rid}.html")
                items.append(
                    f"""<li class="why-item">
  <div class="why-item__head">
    <h3><a href="{h(href)}">{h(m.get('title', rid))}</a></h3>
    <div class="badge-row">
      {badge(str(m.get('severity', '')), severity_tone(str(m.get('severity', '')))) if m.get('severity') else ''}
      {verification_badge(str(m['verification'])) if m.get('verification') else ''}
    </div>
  </div>
  <p>{h(m.get('summary', ''))}</p>
  <p class="why-item__links">{self.functionality_chips(page, self.content.resolve_functionality(m.get('functionality')))}</p>
</li>"""
                )
            sections.append(
                f'<section class="why-group" aria-labelledby="why-{h(mtype)}"><h2 id="why-{h(mtype)}">{h(label)}</h2><ul class="why-list">{"".join(items)}</ul></section>'
            )
        body = f"""<article class="view view-why">
<header class="page-head reading-column">
  <p class="eyebrow">Decisions &amp; rationale</p>
  <h1>The reasoning behind the software</h1>
  <p class="lede">Decisions, constraints, assumptions, warnings, and discoveries — each connected to the functionality it explains.</p>
</header>
{''.join(sections)}
</article>"""
        self.register(page, body, "why", "Decisions & rationale")

    def _gen_why_detail(self, mtype: str, rid: str) -> None:
        page = f"why/{mtype}/{rid}.html"
        rec = self.content.memory[mtype][rid]
        m = rec.meta
        type_labels = {
            "decisions": "Decisions",
            "constraints": "Constraints",
            "assumptions": "Assumptions",
            "warnings": "Warnings",
            "discoveries": "Discoveries",
            "changes": "Changes",
        }
        body = f"""<article class="view view-doc">
<p class="breadcrumb"><a href="{h(self._page_href(page, 'why/index.html'))}">← Decisions &amp; rationale</a></p>
<header class="page-head reading-column">
  <p class="eyebrow">{h(type_labels.get(mtype, mtype))}</p>
  <h1>{h(m.get('title', rid))}</h1>
  <p class="lede">{h(m.get('summary', ''))}</p>
  <div class="badge-row">
    {badge(str(m.get('status', '')), 'info') if m.get('status') else ''}
    {badge('Severity: ' + str(m.get('severity', '')), severity_tone(str(m.get('severity', '')))) if m.get('severity') else ''}
    {verification_badge(str(m['verification'])) if m.get('verification') else ''}
  </div>
</header>
<div class="detail-grid">
  <div class="detail-main"><div class="prose reading-column">{rec.html}</div></div>
  <aside class="detail-rail" aria-label="Connections">
    <div class="rail-card"><h2>Affects functionality</h2><p>{self.functionality_chips(page, self.content.resolve_functionality(m.get('functionality')))}</p></div>
    {f'<div class="rail-card"><h2>Files</h2><p>{self.file_chips(m.get("files"))}</p></div>' if m.get('files') else ''}
  </aside>
</div>
</article>"""
        self.register(page, body, "why", str(m.get("title", rid)))

    def _gen_handoff(self) -> None:
        page = "handoff/index.html"
        handoff = self.content.handoff
        sessions = self.content.sessions
        handoff_block = (
            '<p class="empty-state">No handoff recorded.</p>'
            if not handoff
            else f"""<section class="doc-section callout callout--work">
  <div class="badge-row">
    {badge('Verification: ' + str(handoff.meta.get('verification_state', '')).replace('-', ' '), 'info') if handoff.meta.get('verification_state') else ''}
    <span class="muted">Updated {h(handoff.meta.get('updated', 'unknown'))}</span>
  </div>
  <div class="prose reading-column">{handoff.html}</div>
</section>"""
        )
        session_items = []
        for s in sessions:
            m = s.meta
            session_items.append(
                f"""<li class="why-item">
  <div class="why-item__head">
    <h3>{h(m.get('title', m.get('id', 'Session')))}</h3>
    <div class="badge-row">
      {verification_badge(str(m['verification'])) if m.get('verification') else ''}
      <span class="muted">{h(m.get('date', ''))}</span>
    </div>
  </div>
  <p>{h(m.get('summary', ''))}</p>
  <p class="why-item__links">{self.functionality_chips(page, self.content.resolve_functionality(m.get('functionality')))}</p>
</li>"""
            )
        body = f"""<article class="view view-doc">
<header class="page-head reading-column">
  <p class="eyebrow">AI handoff</p>
  <h1>Start here before you change anything</h1>
  <p class="lede">Everything the next human or AI session needs to avoid reconstructing the project incorrectly.</p>
</header>
{handoff_block}
{f'<section class="doc-section"><h2>Recent work sessions</h2><ul class="why-list">{"".join(session_items)}</ul></section>' if session_items else ''}
<nav class="cross-links" aria-label="Related views">
  <a class="btn" href="{h(self._page_href(page, 'current-work/index.html'))}">Current AI work →</a>
  <a class="btn" href="{h(self._page_href(page, 'index.html'))}">Overview →</a>
</nav>
</article>"""
        self.register(page, body, "handoff", "AI handoff")

    def _gen_reference(self) -> None:
        page = "reference/index.html"
        issues = self.content.issues
        errors = [i for i in issues if i["level"] == "error"]
        warnings = [i for i in issues if i["level"] == "warn"]
        if not issues:
            validation = '<p class="callout callout--ok">No validation issues detected. Every record parses, every relationship resolves.</p>'
        else:
            validation = (
                f'<p class="badge-row">{badge(str(len(errors)) + " error(s)", "danger" if errors else "ok")} '
                f'{badge(str(len(warnings)) + " warning(s)", "warn" if warnings else "ok")}</p>'
            )
            if errors:
                validation += '<h3>Errors</h3><ul class="issue-list issue-list--error">' + "".join(
                    f"<li>{h(i['message'])}</li>" for i in errors
                ) + "</ul>"
            if warnings:
                validation += '<h3>Warnings</h3><ul class="issue-list issue-list--warn">' + "".join(
                    f"<li>{h(i['message'])}</li>" for i in warnings
                ) + "</ul>"
        status_badges = "".join(status_badge(k) for k in STATUS_VOCAB)
        ver_badges = "".join(verification_badge(k) for k in VERIFICATION_VOCAB)
        safety_badges = "".join(
            badge(v, safety_tone(k)) for k, v in SAFETY_VOCAB.items()
        )
        manifest = self.content.manifest
        body = f"""<article class="view view-doc">
<header class="page-head reading-column">
  <p class="eyebrow">Reference</p>
  <h1>Content model &amp; diagnostics</h1>
  <p class="lede">How this guide's content is structured, the vocabularies it validates against, and any problems detected.</p>
</header>
<section class="doc-section" id="validation"><h2>Content validation</h2>{validation}</section>
<section class="doc-section"><h2>Statuses</h2><p class="badge-row">{status_badges}</p>
<h2>Verification / provenance states</h2><p class="badge-row">{ver_badges}</p>
<h2>File safety levels</h2><p class="badge-row">{safety_badges}</p></section>
<section class="doc-section">
  <h2>Technical constraints</h2>
  <ul>
    <li>Static HTML generated from <code>.vibekb/</code> content.</li>
    <li>Hosted from <code>/docs</code> for GitHub Pages.</li>
    <li>Client-side search; no backend required to read the guide.</li>
  </ul>
  <p class="muted">Content model version {h(manifest.get('vibekb_version', 'unknown'))}, updated {h(manifest.get('updated', 'unknown'))}.</p>
</section>
</article>"""
        self.register(page, body, "reference", "Reference")

    def _gen_search(self) -> None:
        page = "search/index.html"
        body = """<article class="view view-doc">
<header class="page-head reading-column">
  <p class="eyebrow">Search</p>
  <h1>Search the guide</h1>
  <p class="lede">Find functionality, files, components, and repository memory.</p>
</header>
<form class="filters wide-section" role="search">
  <label>Query<input type="search" id="search-query" name="q" placeholder="Type to search…" autocomplete="off"></label>
</form>
<div id="search-results" class="wide-section"></div>
<p class="muted" id="search-empty" hidden>No results found.</p>
</article>"""
        self.register(page, body, "search", "Search")

    def _write_search_index(self, path: Path) -> None:
        items: list[dict[str, str]] = []
        for fid, rec in self.content.functionality.items():
            items.append(
                {
                    "title": str(rec.meta.get("title", fid)),
                    "summary": str(rec.meta.get("summary", "")),
                    "type": "functionality",
                    "url": f"functionality/{fid}.html",
                    "body": rec.body[:2000],
                }
            )
        for f in self.content.files:
            items.append(
                {
                    "title": str(f.get("path", "")),
                    "summary": str(f.get("purpose", "")),
                    "type": "file",
                    "url": f"files/index.html#{f.get('path', '')}",
                    "body": "",
                }
            )
        for name, doc in self.content.system.items():
            items.append(
                {
                    "title": str(doc.meta.get("title", name)),
                    "summary": str(doc.meta.get("summary", "")),
                    "type": "component",
                    "url": "how-it-works/index.html",
                    "body": doc.body[:2000],
                }
            )
        for mtype, records in self.content.memory.items():
            for rid, rec in records.items():
                items.append(
                    {
                        "title": str(rec.meta.get("title", rid)),
                        "summary": str(rec.meta.get("summary", "")),
                        "type": mtype.rstrip("s"),
                        "url": f"why/{mtype}/{rid}.html",
                        "body": rec.body[:2000],
                    }
                )
        path.write_text(json.dumps(items, indent=2), encoding="utf-8")


def view_to_page(view: str) -> str:
    return {
        "overview": "index.html",
        "functionality": "functionality/index.html",
        "how-it-works": "how-it-works/index.html",
        "data": "data/index.html",
        "files": "files/index.html",
        "current-work": "current-work/index.html",
        "changes": "changes/index.html",
        "why": "why/index.html",
        "handoff": "handoff/index.html",
        "reference": "reference/index.html",
        "search": "search/index.html",
    }.get(view, "index.html")


def main() -> int:
    content = Content(CONTENT_ROOT)
    content.load()
    generator = SiteGenerator(content)
    generator.generate()
    errors = [i for i in content.issues if i["level"] == "error"]
    print(f"Generated {len(generator.pages)} pages into {DOCS_ROOT}")
    print(f"Validation: {len(errors)} error(s), {len(content.issues) - len(errors)} warning(s)")
    for issue in content.issues:
        print(f"  [{issue['level']}] {issue['message']}")
    return 1 if errors else 0


if __name__ == "__main__":
    sys.exit(main())
