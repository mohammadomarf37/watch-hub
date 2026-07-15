import 'package:flutter_test/flutter_test.dart';
import 'package:watch_hub_frontend/main.dart';
import 'package:watch_hub_frontend/providers/auth_provider.dart';

void main() {
  testWidgets('App starts smoke test', (WidgetTester tester) async {
    // Build our app and trigger a frame.
    await tester.pumpWidget(MyApp(authProvider: AuthProvider()));

    // Verify that the title WATCHHUB is present on splash screen.
    // The splash screen displays WATCHHUB in capitalized letters.
    expect(find.text('WATCHHUB'), findsOneWidget);

    // Advance the mock clock to let the splash timer run and complete
    await tester.pump(const Duration(milliseconds: 3000));
  });
}
