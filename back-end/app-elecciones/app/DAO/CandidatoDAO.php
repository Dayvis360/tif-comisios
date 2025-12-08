<?php

namespace App\DAO;

use App\Models\Candidato;

class CandidatoDAO
{
    //Obtener todos los candidatos
    public function getAll(): array
    {
        return Candidato::with(['lista.provincia'])
            ->orderBy('lista_id', 'asc')
            ->orderBy('orden_en_lista', 'asc')
            ->get()
            ->map(function($candidato) {
                $array = $candidato->toArray();
                $array['lista_nombre'] = $candidato->lista->nombre ?? null;
                $array['lista_cargo'] = $candidato->lista->cargo ?? null;
                $array['provincia_nombre'] = $candidato->lista->provincia->nombre ?? null;
                return $array;
            })
            ->toArray();
    }

    //Buscar candidato por ID
    public function findById(int $id): ?object
    {
        $candidato = Candidato::find($id);
        return $candidato ? (object)$candidato->toArray() : null;
    }

    //Buscar candidatos por lista
    public function findByLista(int $listaId): array
    {
        return Candidato::where('lista_id', $listaId)
            ->orderBy('orden_en_lista', 'asc')
            ->get()
            ->toArray();
    }

    //Insertar nuevo candidato
    public function insert(array $data): int
    {
        $candidato = Candidato::create([
            'nombre' => $data['nombre'],
            'orden_en_lista' => $data['orden_en_lista'],
            'lista_id' => $data['lista_id'],
        ]);
        
        return $candidato->id;
    }

    //Actualizar candidato
    public function update(int $id, array $data): bool
    {
        return Candidato::where('id', $id)
            ->update([
                'nombre' => $data['nombre'],
                'orden_en_lista' => $data['orden_en_lista'],
                'lista_id' => $data['lista_id'],
            ]);
    }

    //Eliminar candidato
    public function delete(int $id): bool
    {
        return Candidato::destroy($id) > 0;
    }

    //Verificar si existe orden en lista
    public function existeOrdenEnLista(int $listaId, int $orden, ?int $excludeId = null): bool
    {
        $query = Candidato::where('lista_id', $listaId)
            ->where('orden_en_lista', $orden);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    //Obtener máximo orden en lista
    public function getMaxOrdenEnLista(int $listaId): int
    {
        return Candidato::where('lista_id', $listaId)
            ->max('orden_en_lista') ?? 0;
    }

    //Contar candidatos
    public function count(): int
    {
        return Candidato::count();
    }

    //Contar candidatos por lista
    public function countByLista(int $listaId): int
    {
        return Candidato::where('lista_id', $listaId)->count();
    }
}
