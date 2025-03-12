import os
import pdfplumber
import ollama

# Ruta de la carpeta donde están los PDFs (misma ubicación que el script)
script_dir = os.path.dirname(os.path.abspath(__file__))
pdf_folder = os.path.join(script_dir, "documentos_pdf")

# Verifica si la carpeta existe
def check_folder_exists(folder_path):
    return os.path.exists(folder_path) and os.path.isdir(folder_path)

# Función para leer y extraer texto de los PDFs en la carpeta
def load_pdfs(folder_path):
    all_text = ""
    if not check_folder_exists(folder_path):
        print(f"⚠️ La carpeta '{folder_path}' no existe.")
        return all_text
    
    # Mostrar los archivos en la carpeta
    print(f"Archivos en la carpeta '{folder_path}':")
    files_to_load = [f for f in os.listdir(folder_path) if f.lower().endswith('.pdf')]
    
    if not files_to_load:
        print("⚠️ No se han encontrado archivos PDF en la carpeta.")
        return all_text

    for file in files_to_load:
        print(f"- {file}")

    # Procesar cada archivo PDF
    for file in files_to_load:
        pdf_path = os.path.join(folder_path, file)
        print(f"\nProcesando el archivo PDF: {pdf_path}")
        try:
            with pdfplumber.open(pdf_path) as pdf:
                for page in pdf.pages:
                    text = page.extract_text()
                    if text:
                        all_text += text + "\n"  # Agregar el texto al total
                    else:
                        print(f"⚠️ No se pudo extraer texto de la página de '{file}'")
        except Exception as e:
            print(f"Error al procesar '{file}': {e}")

    return all_text

# Cargar los PDFs
pdf_text = load_pdfs(pdf_folder)
