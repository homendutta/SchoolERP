import 'package:flutter/material.dart';
import '../../app/navigation/menu_catalog.dart';
import '../../app/theme/app_colors.dart';
import '../../core/auth/session.dart';

/// DashboardShell — the single, role-adaptive app shell that preserves the
/// reference application's structure on mobile: an app bar, a grouped navigation
/// drawer, and a bottom navigation bar. The signed-in role drives the menu.
///
/// Foundation stage: renders the shell and an active-module placeholder. Module
/// screens mount into the body as modules are built.
class DashboardShell extends StatefulWidget {
  const DashboardShell({super.key, required this.session});

  final Session session;

  @override
  State<DashboardShell> createState() => _DashboardShellState();
}

class _DashboardShellState extends State<DashboardShell> {
  late String _activeMenu = landingMenu(widget.session.role);

  List<MenuItem> get _items => menuForRole(widget.session.role);

  void _select(String id) {
    setState(() => _activeMenu = id);
    Navigator.of(context).maybePop(); // close drawer if open
  }

  @override
  Widget build(BuildContext context) {
    final active = menuCatalog[_activeMenu] ?? menuCatalog['dashboard']!;
    final bottomItems = _items.take(4).toList();
    final bottomIndex =
        bottomItems.indexWhere((i) => i.id == _activeMenu).clamp(0, bottomItems.length - 1);

    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            Icon(active.icon, size: 20),
            const SizedBox(width: 8),
            Text(active.label),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.search),
            tooltip: 'Search',
            onPressed: () {},
          ),
          IconButton(
            icon: const Icon(Icons.logout),
            tooltip: 'Sign out',
            onPressed: () => widget.session.logout(),
          ),
        ],
      ),
      drawer: _NavigationDrawer(
        session: widget.session,
        activeMenu: _activeMenu,
        onSelect: _select,
      ),
      body: _PlaceholderBody(active: active),
      bottomNavigationBar: NavigationBar(
        selectedIndex: bottomIndex,
        onDestinationSelected: (i) => _select(bottomItems[i].id),
        destinations: bottomItems
            .map((i) => NavigationDestination(icon: Icon(i.icon), label: i.label))
            .toList(),
      ),
    );
  }
}

class _NavigationDrawer extends StatelessWidget {
  const _NavigationDrawer({
    required this.session,
    required this.activeMenu,
    required this.onSelect,
  });

  final Session session;
  final String activeMenu;
  final void Function(String id) onSelect;

  @override
  Widget build(BuildContext context) {
    final items = menuForRole(session.role);

    // Group items in order, preserving the reference grouping.
    final groups = <String, List<MenuItem>>{};
    for (final item in items) {
      groups.putIfAbsent(item.group, () => []).add(item);
    }

    return Drawer(
      backgroundColor: AppColors.navyPrimary,
      child: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  const Icon(Icons.school, color: AppColors.navyAccent),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      session.fullName,
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ),
            const Divider(color: Colors.white24, height: 1),
            Expanded(
              child: ListView(
                children: [
                  for (final entry in groups.entries) ...[
                    Padding(
                      padding: const EdgeInsets.fromLTRB(16, 14, 16, 4),
                      child: Text(
                        groupLabels[entry.key] ?? entry.key,
                        style: const TextStyle(
                          color: Colors.white38,
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          letterSpacing: 0.8,
                        ),
                      ),
                    ),
                    for (final item in entry.value)
                      ListTile(
                        dense: true,
                        leading: Icon(item.icon,
                            color: item.id == activeMenu ? Colors.white : Colors.white70, size: 20),
                        title: Text(
                          item.label,
                          style: TextStyle(
                            color: item.id == activeMenu ? Colors.white : Colors.white70,
                          ),
                        ),
                        selected: item.id == activeMenu,
                        selectedTileColor: AppColors.navyAccent,
                        onTap: () => onSelect(item.id),
                      ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _PlaceholderBody extends StatelessWidget {
  const _PlaceholderBody({required this.active});

  final MenuItem active;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Card(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Icon(active.icon, color: AppColors.navyPrimary),
                  const SizedBox(width: 10),
                  Text(active.label,
                      style: const TextStyle(
                          fontSize: 18, fontWeight: FontWeight.w600, color: AppColors.navyPrimary)),
                ],
              ),
              const SizedBox(height: 8),
              const Text(
                'Engineering foundation — module screens mount here as they are '
                'built. The theme, navigation, and role-adaptive shell are ready.',
                style: TextStyle(color: Colors.grey),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
