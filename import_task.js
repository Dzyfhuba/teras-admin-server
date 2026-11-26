import { readFileSync } from "node:fs";
import { applicationDefault, initializeApp } from "firebase-admin/app";
import { getFirestore } from "firebase-admin/firestore";

initializeApp({
  credential: applicationDefault(),
});

const db = getFirestore();
const tasks = JSON.parse(
  readFileSync(new URL("./tugas_backend_laravel.json", import.meta.url), "utf8")
);

if (!Array.isArray(tasks)) {
  throw new TypeError("tugas_backend_laravel.json must contain an array");
}

const batch = db.batch();

tasks.forEach((task) => {
  if (typeof task.id !== "string" || task.id.length === 0) {
    throw new TypeError("Every task must have a non-empty string id");
  }

  const documentRef = db.collection("tasks").doc(task.id);
  batch.set(documentRef, task, { merge: true });
});

await batch.commit();
console.log(`Berhasil mengimpor ${tasks.length} tugas`);
