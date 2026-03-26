<?php
$pageTitle = 'epiGuard - Infrações';
ob_start();
?>

<!-- Header -->
<header class="header">
    <div class="page-title">
        <h1>Infrações</h1>
        <p>Gestão de ocorrências e infrações de EPI</p>
    </div>
    <div class="header-actions">
        <button class="btn-primary" onclick="alert('Em breve: Exportar PDF')">
            <i class="fa-solid fa-download"></i> Exportar
        </button>
    </div>
</header>

<div class="page-content">
    <!-- Summary Cards -->
    <div class="summary-row">
        <div class="summary-card">
            <div class="summary-icon red">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="summary-info">
                <span class="summary-label">Total Infrações</span>
                <span class="summary-value" id="totalInfracoes"><?= count($infractions) ?></span>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon amber">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div class="summary-info">
                <span class="summary-label">Pendentes</span>
                <span class="summary-value" id="totalPendentes"><?= count(array_filter($infractions, fn($i) => ($i['status'] ?? '') === 'pendente')) ?: 0 ?></span>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon green">
                <i class="fa-solid fa-check-circle"></i>
            </div>
            <div class="summary-info">
                <span class="summary-label">Resolvidas</span>
                <span class="summary-value" id="totalResolvidas"><?= count(array_filter($infractions, fn($i) => ($i['status'] ?? '') === 'resolvido')) ?: 0 ?></span>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon blue">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="summary-info">
                <span class="summary-label">Funcionários Afetados</span>
                <span class="summary-value" id="totalAlunos"><?= count(array_unique(array_column($infractions, 'funcionario_nome'))) ?></span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <form action="<?= BASE_PATH ?>/infractions" method="GET" class="filter-bar">
        <input type="text" name="search" id="searchInput" placeholder="🔍 Buscar funcionário ou setor..." value="<?= htmlspecialchars($filters['search']) ?>">
        <select name="periodo" id="filterPeriodo">
            <option value="todos" <?= $filters['periodo'] === 'todos' ? 'selected' : '' ?>>Todos os períodos</option>
            <option value="hoje" <?= $filters['periodo'] === 'hoje' ? 'selected' : '' ?>>Hoje</option>
            <option value="semana" <?= $filters['periodo'] === 'semana' ? 'selected' : '' ?>>Esta Semana</option>
            <option value="mes" <?= $filters['periodo'] === 'mes' ? 'selected' : '' ?>>Este Mês</option>
        </select>
        <select name="status" id="filterStatus">
            <option value="todos" <?= $filters['status'] === 'todos' ? 'selected' : '' ?>>Todos os Status</option>
            <option value="pendente" <?= $filters['status'] === 'pendente' ? 'selected' : '' ?>>Pendente</option>
            <option value="resolvido" <?= $filters['status'] === 'resolvido' ? 'selected' : '' ?>>Resolvido</option>
        </select>
        <select name="epi" id="filterEpi">
            <option value="todos" <?= $filters['epi'] === 'todos' ? 'selected' : '' ?>>Todos os EPIs</option>
            <?php foreach ($episList as $epiItem): ?>
                <option value="<?= htmlspecialchars($epiItem->getName()) ?>" <?= $filters['epi'] === $epiItem->getName() ? 'selected' : '' ?>>
                    <?= htmlspecialchars($epiItem->getName()) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="visualizacao" id="filterVisualizacao">
            <option value="nome" <?= $filters['visualizacao'] === 'nome' ? 'selected' : '' ?>>Exibir Nome</option>
            <option value="cards" <?= $filters['visualizacao'] === 'cards' ? 'selected' : '' ?>>Exibir Cards</option>
        </select>
        <button type="submit" class="btn-filter">
            <i class="fa-solid fa-filter"></i> Filtrar
        </button>
    </form>

    <!-- Table -->
    <div class="table-card">
        <div class="card-header">
            <h3>Registro de Infrações</h3>
            <span style="font-size: 12px; color: var(--text-muted);" id="tableCount">Mostrando <?= count($infractions) ?> registros</span>
        </div>

        <?php if ($filters['visualizacao'] === 'cards'): ?>
            <div class="cards-grid" id="infractionsCardsGrid">
                <?php foreach ($infractions as $infraction): ?>
                    <div class="infraction-card">
                        <div class="card-image-box">
                            <?php 
                                $photoPath = !empty($infraction['funcionario_foto']) ? BASE_PATH . '/' . $infraction['funcionario_foto'] : BASE_PATH . '/assets/img/default-avatar.png';
                            ?>
                            <img src="<?= $photoPath ?>" alt="<?= htmlspecialchars($infraction['funcionario_nome']) ?>" class="card-employee-photo" onerror="this.src='<?= BASE_PATH ?>/assets/img/default-avatar.png'">
                            <span class="status-badge-premium <?= ($infraction['status'] ?? 'pendente') === 'resolvido' ? 'resolved' : 'pending' ?>">
                                <?= ucfirst($infraction['status'] ?? 'Pendente') ?>
                            </span>
                        </div>
                        <div class="card-content-premium">
                            <h4 class="employee-name"><?= htmlspecialchars($infraction['funcionario_nome']) ?></h4>
                            <div class="info-row-premium">
                                <i class="fa-solid fa-briefcase"></i>
                                <span>Setor: <?= htmlspecialchars($infraction['setor_sigla'] ?? 'N/A') ?></span>
                            </div>
                            <div class="info-row-premium">
                                <i class="fa-solid fa-shield-halved"></i>
                                <span>EPI: <?= htmlspecialchars($infraction['epi_nome'] ?? 'N/A') ?></span>
                            </div>
                            <div class="info-row-premium">
                                <i class="fa-solid fa-clock"></i>
                                <span><?= date('d/m/Y - H:i', strtotime($infraction['data_hora'])) ?></span>
                            </div>
                            <div class="card-footer-premium">
                                <button class="btn-card-action" title="Ver detalhes"><i class="fa-solid fa-eye"></i></button>
                                <?php if (($infraction['status'] ?? 'pendente') !== 'resolvido'): ?>
                                    <button class="btn-card-action success" title="Resolver"><i class="fa-solid fa-check"></i></button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <table class="data-table" id="infractionsTable">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Funcionário</th>
                        <th>Setor</th>
                        <th>EPI</th>
                        <th>Horário</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="infractionsTableBody">
                    <?php if (empty($infractions)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                Nenhuma infração encontrada com os filtros selecionados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($infractions as $infraction): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($infraction['data_hora'])) ?></td>
                                <td class="employee-cell">
                                    <?php if ($filters['visualizacao'] === 'foto'): ?>
                                        <div class="employee-avatar-wrapper">
                                            <?php 
                                                $photoPath = !empty($infraction['funcionario_foto']) ? BASE_PATH . '/' . $infraction['funcionario_foto'] : BASE_PATH . '/assets/img/default-avatar.png';
                                            ?>
                                            <img src="<?= $photoPath ?>" alt="<?= htmlspecialchars($infraction['funcionario_nome']) ?>" class="employee-avatar" onerror="this.src='<?= BASE_PATH ?>/assets/img/default-avatar.png'">
                                        </div>
                                    <?php else: ?>
                                        <span style="font-weight: 600;"><?= htmlspecialchars($infraction['funcionario_nome']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($infraction['setor_sigla'] ?? 'N/A') ?></td>
                                <td data-epi="<?= strtolower($infraction['epi_nome'] ?? '') ?>"><?= htmlspecialchars($infraction['epi_nome'] ?? 'N/A') ?></td>
                                <td><?= date('H:i', strtotime($infraction['data_hora'])) ?></td>
                                <td data-status="<?= htmlspecialchars($infraction['status'] ?? 'pendente') ?>">
                                    <span class="status-dot <?= ($infraction['status'] ?? 'pendente') === 'resolvido' ? 'resolved' : 'pending' ?>"></span> 
                                    <?= ucfirst($infraction['status'] ?? 'Pendente') ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button class="btn-action" title="Ver detalhes"><i class="fa-solid fa-eye"></i></button>
                                        <?php if (($infraction['status'] ?? 'pendente') !== 'resolvido'): ?>
                                            <button class="btn-action" title="Resolver"><i class="fa-solid fa-check"></i></button>
                                        <?php endif; ?>
                                        <button class="btn-action danger" title="Excluir" onclick="deleteInfraction(<?= $infraction['id'] ?>, this)"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
    function deleteInfraction(id, btn) {
        if (confirm('Deseja realmente excluir este registro de infração?')) {
            const row = btn.closest('tr');
            row.style.opacity = '0';
            row.style.transform = 'translateX(20px)';
            
            setTimeout(() => {
                row.remove();
            }, 300);
        }
    }
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>
