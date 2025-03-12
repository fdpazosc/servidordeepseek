/*const express = require('express');
const app = express();
const port = 3000;

app.get('/', (req, res) => {
  res.send('Servidor local funcionando en Windows 11!');
});

app.listen(port, () => {
  console.log(`Servidor corriendo en http://localhost:${port}`);
});*/

const express = require('express');
const axios = require('axios');
const app = express();
const port = 3000;

// Middleware para parsear JSON
app.use(express.json());

// Variable para almacenar el contexto
let conversationContext = ''; // Esta variable almacenará el contexto de la conversación


// Ruta GET para la raíz
app.get('/', (req, res) => {
  res.send('¡Bienvenido al servidor de DeepSeek con Ollama!');
});

// Ruta para interactuar con DeepSeek a través de Ollama
app.post('/api/deepseek', async (req, res) => {
  const { prompt } = req.body;
  if (!prompt) {
    return res.status(400).send('El campo "prompt" es requerido');
  }

  try {
    // Combinar el contexto previo con el nuevo prompt
    const fullPrompt = conversationContext + '\n' + prompt;
    // Enviar solicitud a la API de Ollama
    const response = await axios.post('http://localhost:11434/api/generate', {
      model: 'deepseek-r1:8b',
      prompt: "Responde en el mismo idioma el siguiente prompt a la prompt: "+prompt,
      stream: false, // Para recibir una respuesta completa en lugar de un stream
      no_think: true // Para eliminar la sección <think> de la respuesta
    });

    // Devolver la respuesta de Ollama
    let respuesta = response.data.response;

    // Eliminar todo el contenido dentro de <think> incluyendo la etiqueta
    respuesta = respuesta.replace(/<think>[\s\S]*?<\/think>\n?/g, '');

    conversationContext += '\n' + 'Usuario: ' + prompt + '\n' + 'Asistente: ' + respuesta;

    res.send(respuesta);
  } catch (error) {
    console.error(error);
    res.status(500).send('Error al contactar Ollama');
  }
});

// Iniciar el servidor
app.listen(port, () => {
  console.log(`Servidor corriendo en http://localhost:${port}`);
});