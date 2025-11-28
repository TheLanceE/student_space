#!/usr/bin/env python3
"""
EduMind+ Comprehensive Code Audit Script
Analyzes security, architecture, performance, and modernization opportunities
"""

import os
import re
import json
from pathlib import Path
from datetime import datetime
from collections import defaultdict

class CodeAuditor:
    def __init__(self, base_path):
        self.base_path = Path(base_path)
        self.issues = defaultdict(list)
        self.stats = defaultdict(int)
        self.files_analyzed = []
        
    def scan_directory(self):
        """Recursively scan all PHP, JS, CSS files"""
        extensions = {'.php', '.js', '.css', '.html', '.sql'}
        
        for file_path in self.base_path.rglob('*'):
            if file_path.suffix in extensions and file_path.is_file():
                self.files_analyzed.append(str(file_path.relative_to(self.base_path)))
                self.analyze_file(file_path)
                
    def analyze_file(self, file_path):
        """Analyze individual file for issues"""
        try:
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
                rel_path = str(file_path.relative_to(self.base_path))
                
                # Security checks
                self.check_security(content, rel_path)
                
                # Architecture checks
                self.check_architecture(content, rel_path)
                
                # Modern standards
                self.check_modernization(content, rel_path)
                
                # Performance
                self.check_performance(content, rel_path)
                
        except Exception as e:
            self.issues['errors'].append(f"Error reading {file_path}: {str(e)}")
    
    def check_security(self, content, file_path):
        """Check for security vulnerabilities"""
        
        # SQL Injection risks
        if re.search(r'\$_(GET|POST|REQUEST)\[.*?\].*?(mysql_query|query\()', content):
            self.issues['security_critical'].append({
                'file': file_path,
                'type': 'SQL Injection Risk',
                'detail': 'Direct use of user input in SQL queries without parameterization'
            })
            
        # XSS vulnerabilities
        if re.search(r'echo\s+\$_(GET|POST|REQUEST)', content) and 'htmlspecialchars' not in content:
            self.issues['security_high'].append({
                'file': file_path,
                'type': 'XSS Vulnerability',
                'detail': 'Echoing user input without HTML escaping'
            })
            
        # Hardcoded credentials
        if re.search(r"password.*?['\"](?!.*\$)[a-zA-Z0-9]{3,}['\"]", content, re.IGNORECASE):
            self.issues['security_high'].append({
                'file': file_path,
                'type': 'Hardcoded Credentials',
                'detail': 'Potential hardcoded password found'
            })
            
        # Session security
        if 'session_start()' in content and 'session_regenerate_id' not in content:
            self.issues['security_medium'].append({
                'file': file_path,
                'type': 'Session Fixation Risk',
                'detail': 'Session started without regeneration'
            })
            
        # CSRF protection
        if re.search(r'<form.*method=["\']post["\']', content, re.IGNORECASE) and 'csrf' not in content.lower():
            self.issues['security_medium'].append({
                'file': file_path,
                'type': 'Missing CSRF Protection',
                'detail': 'POST form without CSRF token'
            })
            
        # File upload vulnerabilities
        if '$_FILES' in content and 'mime' not in content.lower():
            self.issues['security_high'].append({
                'file': file_path,
                'type': 'Insecure File Upload',
                'detail': 'File upload without MIME type validation'
            })
    
    def check_architecture(self, content, file_path):
        """Check architecture and code organization"""
        
        # Mixed concerns (business logic in views)
        if 'Views/' in file_path and re.search(r'(INSERT|UPDATE|DELETE)\s+', content, re.IGNORECASE):
            self.issues['architecture_high'].append({
                'file': file_path,
                'type': 'Mixed Concerns',
                'detail': 'Database operations in view layer'
            })
            
        # No error handling
        if 'new PDO' in content and 'try' not in content:
            self.issues['architecture_medium'].append({
                'file': file_path,
                'type': 'Missing Error Handling',
                'detail': 'Database connection without try-catch'
            })
            
        # Duplicate code patterns
        if content.count('new PDO(') > 1:
            self.issues['architecture_medium'].append({
                'file': file_path,
                'type': 'Code Duplication',
                'detail': 'Multiple database connection instances'
            })
            
        # Missing input validation
        if '$_POST' in content and 'empty(' not in content and 'isset(' not in content:
            self.issues['architecture_medium'].append({
                'file': file_path,
                'type': 'Missing Input Validation',
                'detail': 'POST data used without validation'
            })
    
    def check_modernization(self, content, file_path):
        """Check for outdated patterns and modernization opportunities"""
        
        # Old MySQL extension
        if 'mysql_' in content:
            self.issues['modernization_critical'].append({
                'file': file_path,
                'type': 'Deprecated MySQL Extension',
                'detail': 'Using deprecated mysql_* functions instead of PDO/mysqli'
            })
            
        # jQuery dependency
        if 'jquery' in content.lower():
            self.issues['modernization_medium'].append({
                'file': file_path,
                'type': 'Legacy jQuery',
                'detail': 'Consider modern vanilla JS or framework'
            })
            
        # No API structure
        if 'Controllers/' in file_path and 'json_encode' not in content:
            self.issues['modernization_low'].append({
                'file': file_path,
                'type': 'No REST API',
                'detail': 'Controller not returning JSON for API consumption'
            })
            
        # Missing modern auth (OAuth, JWT)
        if 'login' in file_path.lower() and 'oauth' not in content.lower() and 'google' not in content.lower():
            self.issues['modernization_medium'].append({
                'file': file_path,
                'type': 'No OAuth Integration',
                'detail': 'Missing modern authentication options (Google, etc.)'
            })
            
        # No password hashing
        if 'password' in content.lower() and 'password_hash' not in content and 'password_verify' not in content:
            self.issues['security_critical'].append({
                'file': file_path,
                'type': 'Plaintext Password Storage',
                'detail': 'Passwords not using password_hash/verify'
            })
    
    def check_performance(self, content, file_path):
        """Check performance issues"""
        
        # N+1 query pattern
        if content.count('->query(') > 3 and 'while' in content:
            self.issues['performance_medium'].append({
                'file': file_path,
                'type': 'Potential N+1 Queries',
                'detail': 'Multiple queries in loop detected'
            })
            
        # Missing indexes hint
        if 'SELECT' in content and 'WHERE' in content and 'INDEX' not in content:
            self.stats['queries_without_index_hint'] += 1
            
        # Large file processing
        if 'file_get_contents' in content and 'memory_limit' not in content:
            self.issues['performance_low'].append({
                'file': file_path,
                'type': 'Memory Risk',
                'detail': 'Large file operations without memory management'
            })
    
    def generate_report(self):
        """Generate comprehensive audit report"""
        report = {
            'audit_date': datetime.now().isoformat(),
            'files_analyzed': len(self.files_analyzed),
            'summary': {
                'critical_issues': len(self.issues['security_critical']) + len(self.issues['modernization_critical']),
                'high_priority': len(self.issues['security_high']) + len(self.issues['architecture_high']),
                'medium_priority': (len(self.issues['security_medium']) + 
                                   len(self.issues['architecture_medium']) + 
                                   len(self.issues['modernization_medium']) +
                                   len(self.issues['performance_medium'])),
                'low_priority': (len(self.issues['modernization_low']) + 
                                len(self.issues['architecture_low']) +
                                len(self.issues['performance_low']))
            },
            'issues_by_category': dict(self.issues),
            'statistics': dict(self.stats),
            'files': self.files_analyzed[:50]  # First 50 files
        }
        
        return report
    
    def save_report(self, output_file):
        """Save report as JSON"""
        report = self.generate_report()
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2, ensure_ascii=False)
        return report

def main():
    base_path = Path(__file__).parent
    auditor = CodeAuditor(base_path)
    
    print("🔍 Starting comprehensive code audit...")
    auditor.scan_directory()
    
    print(f"📊 Analyzed {len(auditor.files_analyzed)} files")
    
    report = auditor.save_report(base_path / 'audit_results.json')
    
    print(f"\n✅ Audit complete!")
    print(f"   Critical: {report['summary']['critical_issues']}")
    print(f"   High: {report['summary']['high_priority']}")
    print(f"   Medium: {report['summary']['medium_priority']}")
    print(f"   Low: {report['summary']['low_priority']}")
    print(f"\n📄 Detailed report saved to: audit_results.json")
    
    return report

if __name__ == '__main__':
    main()
