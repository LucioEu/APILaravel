<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca - Libros</title>
    <style>
        :root {
            --bg: #f6f5f2;
            --ink: #1f2933;
            --muted: #6b7280;
            --accent: #0f766e;
            --accent-2: #134e4a;
            --error: #b91c1c;
            --border: #e5e7eb;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Georgia", "Times New Roman", serif;
            background: radial-gradient(circle at top, #ffffff, var(--bg));
            color: var(--ink);
        }
        .page {
            max-width: 1100px;
            margin: 32px auto 80px;
            padding: 0 20px;
        }
        header {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
        }
        h1 {
            margin: 0;
            font-size: 32px;
            letter-spacing: 0.5px;
        }
        .subtitle { color: var(--muted); }
        .grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 24px;
        }
        .card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px 8px;
            border-bottom: 1px solid var(--border);
            text-align: left;
        }
        th { font-size: 13px; text-transform: uppercase; letter-spacing: 0.06em; color: var(--muted); }
        .actions { display: flex; gap: 8px; }
        button {
            border: none;
            background: var(--accent);
            color: #fff;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
        }
        button.secondary { background: #111827; }
        button.outline {
            background: transparent;
            color: var(--accent-2);
            border: 1px solid var(--accent-2);
        }
        button.danger { background: var(--error); }
        input, select {
            width: 100%;
            padding: 10px 12px;
            margin-top: 6px;
            margin-bottom: 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
        }
        label { font-size: 13px; color: var(--muted); }
        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .status { margin-top: 8px; font-size: 14px; }
        .status.ok { color: var(--accent-2); }
        .status.err { color: var(--error); }
        @media (max-width: 900px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="page">
        <header>
            <div>
                <h1>Biblioteca</h1>
                <div class="subtitle">Gestión de libros vía API REST</div>
            </div>
            <div>
                <button class="secondary" id="btn-refresh">Actualizar lista</button>
            </div>
        </header>

        <div class="grid">
            <section class="card">
                <h2>Listado</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Año</th>
                            <th>Género</th>
                            <th>Disponible</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="libros-body"></tbody>
                </table>
                <div class="status" id="list-status"></div>
            </section>

            <section class="card">
                <h2 id="form-title">Crear libro</h2>
                <form id="libro-form">
                    <input type="hidden" id="libro-id" />
                    <label for="titulo">Título</label>
                    <input id="titulo" type="text" required />

                    <label for="autor">Autor</label>
                    <input id="autor" type="text" required />

                    <div class="row">
                        <div>
                            <label for="anio_publicacion">Año publicación</label>
                            <input id="anio_publicacion" type="number" required />
                        </div>
                        <div>
                            <label for="genero">Género</label>
                            <input id="genero" type="text" required />
                        </div>
                    </div>

                    <label for="disponible">Disponible</label>
                    <select id="disponible">
                        <option value="true">Sí</option>
                        <option value="false">No</option>
                    </select>

                    <div class="actions">
                        <button type="submit" id="btn-submit">Guardar</button>
                        <button type="button" class="outline" id="btn-cancel">Cancelar</button>
                    </div>
                    <div class="status" id="form-status"></div>
                </form>
            </section>
        </div>
    </div>

    <script>
        const apiBase = "{{ url('/api/libros') }}";
        const librosBody = document.getElementById("libros-body");
        const listStatus = document.getElementById("list-status");
        const formStatus = document.getElementById("form-status");
        const formTitle = document.getElementById("form-title");
        const form = document.getElementById("libro-form");
        const btnCancel = document.getElementById("btn-cancel");
        const btnRefresh = document.getElementById("btn-refresh");

        const fields = {
            id: document.getElementById("libro-id"),
            titulo: document.getElementById("titulo"),
            autor: document.getElementById("autor"),
            anio_publicacion: document.getElementById("anio_publicacion"),
            genero: document.getElementById("genero"),
            disponible: document.getElementById("disponible"),
        };

        function setStatus(el, msg, ok = true) {
            el.textContent = msg || "";
            el.className = "status " + (ok ? "ok" : "err");
        }

        function resetForm() {
            fields.id.value = "";
            fields.titulo.value = "";
            fields.autor.value = "";
            fields.anio_publicacion.value = "";
            fields.genero.value = "";
            fields.disponible.value = "true";
            formTitle.textContent = "Crear libro";
            setStatus(formStatus, "");
        }

        async function fetchLibros() {
            listStatus.textContent = "Cargando...";
            try {
                const res = await fetch(apiBase, { headers: { "Accept": "application/json" } });
                const json = await res.json();
                if (!res.ok) throw json;
                renderLibros(json.data || []);
                setStatus(listStatus, `Total: ${json.data.length} libros`, true);
            } catch (err) {
                setStatus(listStatus, "Error al cargar libros", false);
            }
        }

        function renderLibros(libros) {
            librosBody.innerHTML = "";
            if (!libros.length) {
                librosBody.innerHTML = "<tr><td colspan='7'>Sin registros</td></tr>";
                return;
            }
            for (const libro of libros) {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${libro.id}</td>
                    <td>${libro.titulo}</td>
                    <td>${libro.autor}</td>
                    <td>${libro.anio_publicacion}</td>
                    <td>${libro.genero}</td>
                    <td>${libro.disponible ? "Sí" : "No"}</td>
                    <td class="actions">
                        <button class="outline" data-action="edit" data-id="${libro.id}">Editar</button>
                        <button class="danger" data-action="delete" data-id="${libro.id}">Eliminar</button>
                    </td>
                `;
                librosBody.appendChild(tr);
            }
        }

        async function createLibro(payload) {
            const res = await fetch(apiBase, {
                method: "POST",
                headers: { "Content-Type": "application/json", "Accept": "application/json" },
                body: JSON.stringify(payload),
            });
            const json = await res.json();
            if (!res.ok) throw json;
            return json;
        }

        async function updateLibro(id, payload) {
            const res = await fetch(`${apiBase}/${id}`, {
                method: "PUT",
                headers: { "Content-Type": "application/json", "Accept": "application/json" },
                body: JSON.stringify(payload),
            });
            const json = await res.json();
            if (!res.ok) throw json;
            return json;
        }

        async function deleteLibro(id) {
            const res = await fetch(`${apiBase}/${id}`, {
                method: "DELETE",
                headers: { "Accept": "application/json" },
            });
            const json = await res.json();
            if (!res.ok) throw json;
            return json;
        }

        function toPayload() {
            return {
                titulo: fields.titulo.value.trim(),
                autor: fields.autor.value.trim(),
                anio_publicacion: Number(fields.anio_publicacion.value),
                genero: fields.genero.value.trim(),
                disponible: fields.disponible.value === "true",
            };
        }

        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            setStatus(formStatus, "Guardando...");
            try {
                const payload = toPayload();
                const id = fields.id.value;
                if (id) {
                    await updateLibro(id, payload);
                    setStatus(formStatus, "Libro actualizado correctamente", true);
                } else {
                    await createLibro(payload);
                    setStatus(formStatus, "Libro creado correctamente", true);
                }
                await fetchLibros();
                resetForm();
            } catch (err) {
                if (err && err.errors) {
                    const first = Object.values(err.errors)[0];
                    setStatus(formStatus, first ? first[0] : "Error de validación", false);
                } else {
                    setStatus(formStatus, "Error al guardar", false);
                }
            }
        });

        btnCancel.addEventListener("click", () => resetForm());
        btnRefresh.addEventListener("click", () => fetchLibros());

        librosBody.addEventListener("click", async (e) => {
            const btn = e.target.closest("button");
            if (!btn) return;
            const action = btn.dataset.action;
            const id = btn.dataset.id;
            if (action === "edit") {
                try {
                    const res = await fetch(`${apiBase}/${id}`, { headers: { "Accept": "application/json" } });
                    const json = await res.json();
                    if (!res.ok) throw json;
                    const libro = json.data;
                    fields.id.value = libro.id;
                    fields.titulo.value = libro.titulo;
                    fields.autor.value = libro.autor;
                    fields.anio_publicacion.value = libro.anio_publicacion;
                    fields.genero.value = libro.genero;
                    fields.disponible.value = libro.disponible ? "true" : "false";
                    formTitle.textContent = "Editar libro";
                } catch (err) {
                    setStatus(listStatus, "No se pudo cargar el libro", false);
                }
            }
            if (action === "delete") {
                if (!confirm("¿Eliminar este libro?")) return;
                try {
                    await deleteLibro(id);
                    await fetchLibros();
                    setStatus(listStatus, "Libro eliminado", true);
                } catch (err) {
                    setStatus(listStatus, "Error al eliminar", false);
                }
            }
        });

        fetchLibros();
    </script>
</body>
</html>
