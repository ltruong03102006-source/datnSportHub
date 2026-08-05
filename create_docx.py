from zipfile import ZipFile, ZIP_DEFLATED
from xml.sax.saxutils import escape
from pathlib import Path

output_path = Path(r't:\Dự án tốt nghiệp\datnSportHub\PhanTichChucNang.docx')

paragraphs = [
    'Phân tích chức năng hệ thống đặt sân thể thao',
    '',
    '1. Đăng ký',
    'Người dùng đăng ký tài khoản để trở thành thành viên có thể bình luận, đặt sân và theo dõi đơn hàng.',
    'Thông tin cần nhập: họ tên, email, số điện thoại, mật khẩu, ...',
    'Kết quả: đăng ký thành công hoặc thất bại, nếu thành công có thể tự động đăng nhập.',
    '',
    '2. Đăng nhập và đăng xuất',
    'Người dùng đăng nhập để truy cập hệ thống và đăng xuất khi kết thúc.',
    '',
    '3. Tìm kiếm và xem sân',
    'Người dùng có thể tìm sân theo tên, môn thể thao, khu vực và xếp hạng.',
    '',
    '4. Đặt sân',
    'Người dùng chọn sân, khung giờ, xác nhận đặt sân và thanh toán.',
    '',
    '5. Quản lý booking',
    'Người dùng có thể xem lịch sử, hủy đặt sân, đổi lịch và đánh giá sau khi sử dụng dịch vụ.',
    '',
    '6. Chức năng cho chủ sân',
    'Chủ sân có thể quản lý venue, sân con, khung giờ, booking, đánh giá và gói dịch vụ.',
    '',
    '7. Chức năng cho admin',
    'Admin có thể quản lý người dùng, chủ sân, venue, booking, giao dịch và báo cáo.',
]

body_xml = ''.join(
    f'<w:p><w:pPr><w:pStyle w:val="Normal"/></w:pPr><w:r><w:t xml:space="preserve">{escape(p)}</w:t></w:r></w:p>'
    for p in paragraphs
)

content_types = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
</Types>'''

rels = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>'''

document_xml = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:body>
    {body}
    <w:sectPr>
      <w:pgSz w:w="12240" w:h="15840"/>
      <w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440"/>
    </w:sectPr>
  </w:body>
</w:document>'''.format(body=body_xml)

styles_xml = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:style w:type="paragraph" w:default="1" w:styleId="Normal">
    <w:name w:val="Normal"/>
    <w:qFormat/>
    <w:pPr><w:spacing w:after="200"/></w:pPr>
    <w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/><w:sz w:val="22"/></w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Title">
    <w:name w:val="Title"/>
    <w:basedOn w:val="Normal"/>
    <w:qFormat/>
    <w:pPr><w:spacing w:after="240"/></w:pPr>
    <w:rPr><w:b/><w:sz w:val="28"/></w:rPr>
  </w:style>
</w:styles>'''

with ZipFile(output_path, 'w', ZIP_DEFLATED) as zf:
    zf.writestr('[Content_Types].xml', content_types)
    zf.writestr('_rels/.rels', rels)
    zf.writestr('word/document.xml', document_xml)
    zf.writestr('word/styles.xml', styles_xml)

print(f'Created: {output_path}')
