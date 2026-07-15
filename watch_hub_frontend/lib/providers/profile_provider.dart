import 'package:flutter/material.dart';

class FAQItem {
  final String question;
  final String answer;
  bool isExpanded;

  FAQItem({
    required this.question,
    required this.answer,
    this.isExpanded = false,
  });
}

class ProfileProvider extends ChangeNotifier {
  bool _isLoading = false;

  bool get isLoading => _isLoading;

  // Static list of premium FAQs
  final List<FAQItem> _faqs = [
    FAQItem(
      question: 'Are all watches sold on WatchHub genuine?',
      answer: 'Yes. WatchHub deals exclusively in 100% genuine and original luxury timepieces. Each watch is delivered with its original box, serial number, user manual, and international manufacturer warranty card.',
    ),
    FAQItem(
      question: 'What is the return and exchange policy?',
      answer: 'We offer a 30-day hassle-free return and exchange policy. To be eligible for a return, your watch must be unworn, in the same condition that you received it, with all original protective plastics, tags, and packaging intact.',
    ),
    FAQItem(
      question: 'How long does shipping take and is it insured?',
      answer: 'All orders are shipped via fully insured, signature-required express delivery (DHL Express or FedEx). Domestic orders take 1-3 business days, while international shipping takes 3-7 business days depending on customs.',
    ),
    FAQItem(
      question: 'What does the WatchHub warranty cover?',
      answer: 'In addition to the official manufacturer warranty (typically 2-5 years), WatchHub offers an additional 2-year complimentary warranty covering movement components, dial hands, and structural manufacturing defects.',
    ),
    FAQItem(
      question: 'Can I cancel or modify my order after placing it?',
      answer: 'Orders can be cancelled or modified within 1 hour of placement by contact support or from the Orders tracking interface, provided it has not yet progressed to the "Shipped" status.',
    ),
    FAQItem(
      question: 'What payment methods do you accept?',
      answer: 'For demonstration purposes, we support dummy credit/debit card entries and cash-on-delivery. In production, we integrate Apple Pay, Google Pay, standard Credit Cards, and secure bank transfers.',
    ),
  ];

  List<FAQItem> get faqs => _faqs;

  // Toggle FAQ Expand State
  void toggleFaq(int index) {
    _faqs[index].isExpanded = !_faqs[index].isExpanded;
    notifyListeners();
  }

  // Submit Support Ticket Form
  Future<bool> submitSupportTicket({
    required String subject,
    required String message,
  }) async {
    _isLoading = true;
    notifyListeners();

    // Simulate network submission delay
    await Future.delayed(const Duration(milliseconds: 1500));

    _isLoading = false;
    notifyListeners();
    return true;
  }
}
